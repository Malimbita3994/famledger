<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\Family;
use App\Models\IncomeCategory;
use App\Models\Wallet;
use App\Models\WalletReconciliation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReconciliationController extends Controller
{
    protected function authorizeFamilyMember(Family $family): void
    {
        if (! $family->members()->where('user_id', auth()->id())->exists()) {
            abort(403, 'You do not have access to this family.');
        }
    }

    public function index(Family $family, Request $request)
    {
        $this->authorizeFamilyMember($family);

        $query = $family->reconciliations()->with(['wallet:id,name,currency_code', 'createdBy:id,name']);
        if ($request->filled('wallet_id')) {
            $wallet = $family->wallets()->find($request->wallet_id);
            if ($wallet) {
                $query->where('wallet_id', $wallet->id);
            }
        }
        $reconciliations = $query->orderByDesc('reconciled_at')->paginate(20);
        $wallets = $family->wallets()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'currency_code']);

        return view('families.reconciliations.index', compact('family', 'reconciliations', 'wallets'));
    }

    public function create(Family $family, Request $request)
    {
        $this->authorizeFamilyMember($family);

        $wallets = $family->wallets()->where('status', 'active')
            ->withSum('incomes', 'amount')
            ->withSum('expenses', 'amount')
            ->withSum('incomingTransfers', 'amount')
            ->withSum('outgoingTransfers', 'amount')
            ->orderBy('name')
            ->get();

        if ($wallets->isEmpty()) {
            return redirect()
                ->route('families.wallets.index', $family)
                ->with('error', 'Create at least one wallet before reconciling.');
        }

        $selectedWallet = $request->filled('wallet_id')
            ? $wallets->firstWhere('id', (int) $request->wallet_id)
            : $wallets->first();
        if (! $selectedWallet) {
            $selectedWallet = $wallets->first();
        }

        $lastReconciliation = $selectedWallet
            ? $selectedWallet->reconciliations()->orderByDesc('reconciled_at')->first()
            : null;

        return view('families.reconciliations.create', compact('family', 'wallets', 'selectedWallet', 'lastReconciliation'));
    }

    public function store(Request $request, Family $family)
    {
        $this->authorizeFamilyMember($family);

        $wallet = $family->wallets()->findOrFail($request->input('wallet_id'));

        $validated = $request->validate([
            'wallet_id' => ['required', Rule::exists('wallets', 'id')->where('family_id', $family->id)],
            'actual_balance' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'actual_balance.required' => 'Enter the actual balance you verified (e.g. from cash count or bank app).',
        ]);

        $systemBalance = (float) $wallet->balance;
        $actualBalance = (float) $validated['actual_balance'];
        $difference = round($actualBalance - $systemBalance, 2);

        $incomeAdjustmentCategory = IncomeCategory::whereNull('family_id')->where('name', 'Reconciliation Adjustment')->first();
        $expenseAdjustmentCategory = ExpenseCategory::whereNull('family_id')->where('name', 'Reconciliation Adjustment')->first();
        if (! $incomeAdjustmentCategory || ! $expenseAdjustmentCategory) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Reconciliation Adjustment categories are missing. Please run: php artisan db:seed --class=IncomeCategorySeeder && php artisan db:seed --class=ExpenseCategorySeeder');
        }

        DB::beginTransaction();
        try {
            $reconciliation = $family->reconciliations()->create([
                'wallet_id' => $wallet->id,
                'system_balance' => $systemBalance,
                'actual_balance' => $actualBalance,
                'difference' => $difference,
                'reconciled_at' => now(),
                'method' => 'manual',
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            if ($difference > 0) {
                $family->incomes()->create([
                    'wallet_id' => $wallet->id,
                    'category_id' => $incomeAdjustmentCategory->id,
                    'amount' => $difference,
                    'currency_code' => $wallet->currency_code,
                    'source' => 'Reconciliation surplus',
                    'received_date' => now()->toDateString(),
                    'notes' => 'Adjustment: actual balance was higher than system balance. Reconciled ' . now()->format('M j, Y'),
                    'received_by' => auth()->id(),
                    'created_by' => auth()->id(),
                    'reconciliation_id' => $reconciliation->id,
                ]);
            } elseif ($difference < 0) {
                $family->expenses()->create([
                    'wallet_id' => $wallet->id,
                    'category_id' => $expenseAdjustmentCategory->id,
                    'amount' => abs($difference),
                    'currency_code' => $wallet->currency_code,
                    'description' => 'Reconciliation shortage',
                    'expense_date' => now()->toDateString(),
                    'paid_by' => auth()->id(),
                    'created_by' => auth()->id(),
                    'reconciliation_id' => $reconciliation->id,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $message = $difference === 0.0
            ? 'Wallet reconciled. Balances match.'
            : ($difference > 0
                ? 'Reconciled. Surplus recorded as income adjustment; wallet balance updated.'
                : 'Reconciled. Shortage recorded as expense adjustment; wallet balance updated.');

        return redirect()
            ->route('families.reconciliations.index', $family)
            ->with('success', $message);
    }
}
