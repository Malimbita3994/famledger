<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ExpenseCategory;
use App\Models\Family;
use App\Models\FamilyRole;
use App\Models\IncomeCategory;
use App\Models\SystemLookup;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the settings overview page.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user() && $request->user()->hasRole('Super Admin'), 403);

        $user = $request->user();

        $currentFamily = null;

        if ($user) {
            // 1) Try session-selected family (if any) that the user belongs to
            $currentFamilyId = (int) $request->session()->get('current_family_id');

            if ($currentFamilyId > 0) {
                $currentFamily = $user->families()
                    ->where('families.id', $currentFamilyId)
                    ->first();
            }

            // 2) Fallback: first active family, preferring primary membership
            if (! $currentFamily) {
                $currentFamily = $user->families()
                    ->wherePivot('status', 'active')
                    ->orderByDesc('family_user.is_primary')
                    ->orderBy('family_user.created_at')
                    ->first();
            }
        }

        return view('settings.index', [
            'currentFamily' => $currentFamily,
        ]);
    }

    /**
     * Categories & lookup settings (system-wide lookups).
     */
    public function categories(): View
    {
        abort_unless(auth()->user() && auth()->user()->hasRole('Super Admin'), 403);
        $currencies = config('currencies', []);
        $defaultCurrency = $currencies['default'] ?? null;

        $incomeCategories = IncomeCategory::defaults();
        $expenseCategories = ExpenseCategory::defaults();
        $familyRoles = FamilyRole::orderBy('id')->get();
        $customLookups = SystemLookup::orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        return view('settings.categories', [
            'currencies'       => $currencies,
            'defaultCurrency'  => $defaultCurrency,
            'incomeCategories' => $incomeCategories,
            'expenseCategories'=> $expenseCategories,
            'familyRoles'      => $familyRoles,
            'customLookups'    => $customLookups,
        ]);
    }

    /**
     * Quick-create a lookup item (income, expense, or role) from the top Add button.
     */
    public function storeLookup(Request $request)
    {
        abort_unless($request->user() && $request->user()->hasRole('Super Admin'), 403);
        $validated = $request->validate([
            'type'        => ['required', 'string', 'max:255'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $rawType = trim($validated['type']);
        $normalized = mb_strtolower($rawType);

        // Special-case known lookup types so they integrate with existing tables.
        switch ($normalized) {
            case 'income':
            case 'income category':
            case 'income':
                IncomeCategory::firstOrCreate(
                    ['name' => $validated['name'], 'family_id' => null],
                    ['name' => $validated['name']]
                );
                $message = __('Income category added.');
                break;

            case 'expense':
            case 'expense category':
                ExpenseCategory::firstOrCreate(
                    ['name' => $validated['name'], 'family_id' => null],
                    ['name' => $validated['name']]
                );
                $message = __('Expense category added.');
                break;

            case 'role':
            case 'family role':
                FamilyRole::firstOrCreate(
                    ['name' => $validated['name']],
                    [
                        'description' => $validated['description'] ?? null,
                        'is_system'   => false,
                    ]
                );
                $message = __('Family role added.');
                break;

            default:
                // Generic lookup group stored in system_lookups
                SystemLookup::create([
                    'group'       => $rawType,
                    'name'        => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'sort_order'  => 0,
                    'is_active'   => true,
                ]);
                $message = __('Lookup created.');
        }

        return redirect()
            ->route('settings.categories')
            ->with('success', $message);
    }

    /**
     * Update a generic system lookup (group or name/description).
     */
    public function updateLookup(Request $request, SystemLookup $systemLookup)
    {
        abort_unless($request->user() && $request->user()->hasRole('Super Admin'), 403);
        $validated = $request->validate([
            'group'       => ['required', 'string', 'max:255'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $systemLookup->update([
            'group'       => $validated['group'],
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Lookup updated.'));
    }

    /**
     * Delete a generic system lookup.
     */
    public function destroyLookup(SystemLookup $systemLookup)
    {
        abort_unless(auth()->user() && auth()->user()->hasRole('Super Admin'), 403);
        $systemLookup->delete();

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Lookup deleted.'));
    }

    /**
     * Create a new default income category.
     */
    public function storeIncomeCategory(Request $request)
    {
        abort_unless($request->user() && $request->user()->hasRole('Super Admin'), 403);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        IncomeCategory::firstOrCreate(
            ['name' => $validated['name'], 'family_id' => null],
            ['name' => $validated['name']]
        );

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Income category added.'));
    }

    /**
     * Update a default income category name.
     */
    public function updateIncomeCategory(Request $request, IncomeCategory $incomeCategory)
    {
        abort_unless($request->user() && $request->user()->hasRole('Super Admin'), 403);
        abort_unless($incomeCategory->family_id === null, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $incomeCategory->update(['name' => $validated['name']]);

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Income category updated.'));
    }

    /**
     * Delete a default income category.
     */
    public function destroyIncomeCategory(IncomeCategory $incomeCategory)
    {
        abort_unless(auth()->user() && auth()->user()->hasRole('Super Admin'), 403);
        abort_unless($incomeCategory->family_id === null, 404);

        $incomeCategory->delete();

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Income category deleted.'));
    }

    /**
     * Create a new default expense category.
     */
    public function storeExpenseCategory(Request $request)
    {
        abort_unless($request->user() && $request->user()->hasRole('Super Admin'), 403);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        ExpenseCategory::firstOrCreate(
            ['name' => $validated['name'], 'family_id' => null],
            ['name' => $validated['name']]
        );

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Expense category added.'));
    }

    /**
     * Update a default expense category name.
     */
    public function updateExpenseCategory(Request $request, ExpenseCategory $expenseCategory)
    {
        abort_unless($request->user() && $request->user()->hasRole('Super Admin'), 403);
        abort_unless($expenseCategory->family_id === null, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $expenseCategory->update(['name' => $validated['name']]);

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Expense category updated.'));
    }

    /**
     * Delete a default expense category.
     */
    public function destroyExpenseCategory(ExpenseCategory $expenseCategory)
    {
        abort_unless(auth()->user() && auth()->user()->hasRole('Super Admin'), 403);
        abort_unless($expenseCategory->family_id === null, 404);

        $expenseCategory->delete();

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Expense category deleted.'));
    }

    /**
     * Create a new (custom) family role.
     */
    public function storeFamilyRole(Request $request)
    {
        abort_unless($request->user() && $request->user()->hasRole('Super Admin'), 403);
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        FamilyRole::firstOrCreate(
            ['name' => $validated['name']],
            [
                'description' => $validated['description'] ?? null,
                'is_system'   => false,
            ]
        );

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Family role added.'));
    }

    /**
     * Update an existing family role (description only for system roles).
     */
    public function updateFamilyRole(Request $request, FamilyRole $familyRole)
    {
        abort_unless($request->user() && $request->user()->hasRole('Super Admin'), 403);
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $update = [
            'description' => $validated['description'] ?? null,
        ];

        if (! $familyRole->is_system) {
            $update['name'] = $validated['name'];
        }

        $familyRole->update($update);

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Family role updated.'));
    }

    /**
     * Delete a custom family role (system roles cannot be deleted).
     */
    public function destroyFamilyRole(FamilyRole $familyRole)
    {
        abort_unless(auth()->user() && auth()->user()->hasRole('Super Admin'), 403);
        if ($familyRole->is_system) {
            return redirect()
                ->route('settings.categories')
                ->with('error', __('System roles cannot be deleted.'));
        }

        $familyRole->delete();

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Family role deleted.'));
    }

    /**
     * Notifications settings (placeholder for now).
     */
    public function notifications(): View
    {
        abort_unless(auth()->user() && auth()->user()->hasRole('Super Admin'), 403);
        return view('settings.notifications');
    }

    /**
     * Global audit log (Super Admin / Auditor): whole system or filter by a family the user belongs to.
     */
    public function auditLog(Request $request): View
    {
        abort_unless($request->user() && $request->user()->hasRole('Super Admin'), 403);
        $query = AuditLog::with(['user:id,name,email', 'family:id,name'])
            ->orderByDesc('created_at');

        // Scope: whole system or a specific family (families the user belongs to or owns)
        if ($request->filled('family_id')) {
            $familyId = (int) $request->input('family_id');
            $userFamilies = $request->user()->families()->pluck('families.id')->toArray();
            if (in_array($familyId, $userFamilies, true)) {
                $query->forFamily($familyId);
            }
        }

        if ($request->filled('type')) {
            if ($request->input('type') === AuditLog::TYPE_APPLICATION) {
                $query->application();
            } elseif ($request->input('type') === AuditLog::TYPE_DATABASE) {
                $query->database();
            }
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $logs = $query->paginate(50)->withQueryString();

        // Families the user belongs to (for scope dropdown: "Whole system" or pick a family)
        $families = $request->user()->families()->select('families.id as id', 'families.name as name')->orderBy('families.name')->get();

        return view('settings.audit-log', [
            'logs' => $logs,
            'families' => $families,
        ]);
    }
}

