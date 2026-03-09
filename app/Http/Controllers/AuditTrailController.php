<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AuditTrailController extends Controller
{
    /**
     * Only Super Admin can view any family's audit trail.
     * This keeps the audit trail as a platform-level, independent module.
     */
    protected function authorizeFamilyAuditView(Family $family): void
    {
        $user = auth()->user();

        if (! $user || ! $user->hasRole('Super Admin')) {
            abort(403, 'Only Super Admin can view audit trail.');
        }
    }

    /**
     * Family-scoped audit trail: application and database events for this family.
     * Access: Super Admin and Auditor (any family); Owner and Co-owner (their families only).
     */
    public function index(Request $request, Family $family): View
    {
        $this->authorizeFamilyAuditView($family);

        $query = AuditLog::forFamily($family->id)
            ->with(['user:id,name,email'])
            ->orderByDesc('created_at');

        // Filters
        if ($request->filled('type')) {
            if ($request->input('type') === AuditLog::TYPE_APPLICATION) {
                $query->application();
            } elseif ($request->input('type') === AuditLog::TYPE_DATABASE) {
                $query->database();
            }
        }
        if ($request->filled('user_id')) {
            $query->byUser((int) $request->input('user_id'));
        }
        if ($request->filled('action')) {
            $query->action($request->input('action'));
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $logs = $query->paginate(50)->withQueryString();

        // Member dropdown: Super Admin sees all users (global audit scope).
        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('families.audit-trail.index', [
            'family' => $family,
            'logs' => $logs,
            'users' => $users,
        ]);
    }

    /**
     * Build the same filtered query as index (without pagination) for export.
     */
    protected function buildAuditQuery(Request $request, Family $family)
    {
        $query = AuditLog::forFamily($family->id)
            ->with(['user:id,name,email'])
            ->orderByDesc('created_at');

        if ($request->filled('type')) {
            if ($request->input('type') === AuditLog::TYPE_APPLICATION) {
                $query->application();
            } elseif ($request->input('type') === AuditLog::TYPE_DATABASE) {
                $query->database();
            }
        }
        if ($request->filled('user_id')) {
            $query->byUser((int) $request->input('user_id'));
        }
        if ($request->filled('action')) {
            $query->action($request->input('action'));
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        return $query;
    }

    /**
     * Export audit trail as PDF. Uses same filters as the index page (from query string).
     */
    public function exportPdf(Request $request, Family $family): Response
    {
        $this->authorizeFamilyAuditView($family);

        $logs = $this->buildAuditQuery($request, $family)->limit(500)->get();
        $generatedAt = now()->format('Y-m-d H:i:s');

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('families.audit-trail.pdf', [
            'family' => $family,
            'logs' => $logs,
            'generatedAt' => $generatedAt,
        ]);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'audit-trail-' . $family->id . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
