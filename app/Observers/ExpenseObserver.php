<?php

namespace App\Observers;

use App\Models\Expense;
use App\Services\WalletTransactionService;

class ExpenseObserver
{
    protected WalletTransactionService $transactionService;

    public function __construct(WalletTransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void
    {
        $this->transactionService->recordExpense($expense);
    }

    /**
     * Handle the Expense "updated" event.
     */
    public function updated(Expense $expense): void
    {
        // If amount changed, we might need to adjust transactions
        // For now, we'll handle this in the controller/service layer
    }

    /**
     * Handle the Expense "deleted" event.
     */
    public function deleted(Expense $expense): void
    {
        // Remove associated wallet transactions
        $expense->wallet->transactions()
            ->where('reference_type', 'expense')
            ->where('reference_id', $expense->id)
            ->delete();
    }
}
