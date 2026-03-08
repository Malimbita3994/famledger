<?php

namespace App\Observers;

use App\Models\SavingsContribution;
use App\Services\WalletTransactionService;

class SavingsContributionObserver
{
    protected WalletTransactionService $transactionService;

    public function __construct(WalletTransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Handle the SavingsContribution "created" event.
     */
    public function created(SavingsContribution $contribution): void
    {
        $this->transactionService->recordSavingsContribution($contribution);
    }

    /**
     * Handle the SavingsContribution "updated" event.
     */
    public function updated(SavingsContribution $contribution): void
    {
        // If amount changed, we might need to adjust transactions
        // For now, we'll handle this in the controller/service layer
    }

    /**
     * Handle the SavingsContribution "deleted" event.
     */
    public function deleted(SavingsContribution $contribution): void
    {
        // Remove associated wallet transactions
        $contribution->fromWallet->transactions()
            ->where('reference_type', 'savings_contribution')
            ->where('reference_id', $contribution->id)
            ->delete();
    }
}
