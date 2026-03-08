<?php

namespace App\Observers;

use App\Models\Transfer;
use App\Services\WalletTransactionService;

class TransferObserver
{
    protected WalletTransactionService $transactionService;

    public function __construct(WalletTransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Handle the Transfer "created" event.
     */
    public function created(Transfer $transfer): void
    {
        $this->transactionService->recordTransfer($transfer);
    }

    /**
     * Handle the Transfer "updated" event.
     */
    public function updated(Transfer $transfer): void
    {
        // If amount or wallets changed, we might need to adjust transactions
        // For now, we'll handle this in the controller/service layer
    }

    /**
     * Handle the Transfer "deleted" event.
     */
    public function deleted(Transfer $transfer): void
    {
        // Remove associated wallet transactions
        $transfer->fromWallet->transactions()
            ->where('reference_type', 'transfer')
            ->where('reference_id', $transfer->id)
            ->delete();

        $transfer->toWallet->transactions()
            ->where('reference_type', 'transfer')
            ->where('reference_id', $transfer->id)
            ->delete();
    }
}
