<?php

namespace App\Observers;

use App\Models\Income;
use App\Services\WalletTransactionService;

class IncomeObserver
{
    protected WalletTransactionService $transactionService;

    public function __construct(WalletTransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Handle the Income "created" event.
     */
    public function created(Income $income): void
    {
        $this->transactionService->recordIncome($income);
    }

    /**
     * Handle the Income "updated" event.
     */
    public function updated(Income $income): void
    {
        // If amount changed, we might need to adjust transactions
        // For now, we'll handle this in the controller/service layer
    }

    /**
     * Handle the Income "deleted" event.
     */
    public function deleted(Income $income): void
    {
        // Remove associated wallet transactions
        $income->wallet->transactions()
            ->where('reference_type', 'income')
            ->where('reference_id', $income->id)
            ->delete();
    }
}
