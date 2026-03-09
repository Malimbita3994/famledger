<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Family;
use App\Models\FamilyMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    /**
     * Only Super Admin, Auditor, or family Owner/Co-owner can view audit trail.
     */
    protected function authorizeAuditView(Family $family): void
    {
        $user = auth()->user();
        if ($user->hasRole('Super Admin') || $user->hasRole('Auditor')) {
            return;
        }

        $membership = FamilyMember::where('family_id', $family->id)
            ->where('user_id', $user->id)
            ->with('role')
            ->first();

        $roleName = $membership?->role?->name ?? '';
        if (! in_array($roleName, ['Owner', 'Co-owner', 'Co-Owner'], true)) {
            abort(403, 'Only the owner or co-owner can view this family\'s audit trail.');
        }
    }

    /**
     * Family-scoped audit trail (application + database events).
     */
    public function index(Request $request, Family $family): JsonResponse
    {
        $this->authorizeAuditView($family);

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

        $perPage = min((int) $request->get('per_page', 50), 100);
        $logs = $query->paginate($perPage);

        $items = $logs->getCollection()->map(function (AuditLog $log) {
            return [
                'id' => $log->id,
                'type' => $log->type,
                'action' => $log->action,
                'description' => $log->description,
                'subject_type' => $log->subject_type ? class_basename($log->subject_type) : null,
                'subject_id' => $log->subject_id,
                'properties' => $log->properties,
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                    'email' => $log->user->email,
                ] : null,
                'created_at' => $log->created_at?->toIso8601String(),
                'area' => $log->area,
            ];
        });

        return response()->json([
            'logs' => $items,
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }
}
