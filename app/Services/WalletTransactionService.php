<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing wallet transactions ledger.
 * Ensures all money movements are recorded for accurate balance calculations.
 */
class WalletTransactionService
{
    /**
     * Record a transaction in the wallet ledger.
     */
    public function recordTransaction(
        Wallet $wallet,
        string $transactionType,
        float $amount,
        string $description = null,
        string $referenceType = null,
        int $referenceId = null,
        int $createdBy = null
    ): WalletTransaction {
        return DB::transaction(function () use (
            $wallet,
            $transactionType,
            $amount,
            $description,
            $referenceType,
            $referenceId,
            $createdBy
        ) {
            // Calculate the new balance
            $currentBalance = $wallet->balance;
            $newBalance = $this->calculateNewBalance($currentBalance, $transactionType, $amount);

            // Create the transaction record
            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_type' => $transactionType,
                'amount' => $amount,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'balance_after' => $newBalance,
                'created_by' => $createdBy,
            ]);
        });
    }

    /**
     * Calculate the new balance based on transaction type.
     */
    private function calculateNewBalance(float $currentBalance, string $transactionType, float $amount): float
    {
        return match ($transactionType) {
            'income', 'transfer_in', 'savings_allocation' => $currentBalance + $amount,
            'expense', 'transfer_out', 'savings_contribution' => $currentBalance - $amount,
            default => $currentBalance,
        };
    }

    /**
     * Record income transaction.
     */
    public function recordIncome($income): WalletTransaction
    {
        return $this->recordTransaction(
            $income->wallet,
            'income',
            $income->amount,
            "Income: {$income->description}",
            'income',
            $income->id,
            $income->created_by
        );
    }

    /**
     * Record expense transaction.
     */
    public function recordExpense($expense): WalletTransaction
    {
        return $this->recordTransaction(
            $expense->wallet,
            'expense',
            $expense->amount,
            "Expense: {$expense->description}",
            'expense',
            $expense->id,
            $expense->created_by
        );
    }

    /**
     * Record transfer transactions (outgoing and incoming).
     */
    public function recordTransfer($transfer): array
    {
        $transactions = [];

        // Record outgoing from source wallet
        $transactions[] = $this->recordTransaction(
            $transfer->fromWallet,
            'transfer_out',
            $transfer->amount,
            "Transfer to {$transfer->toWallet->name}: {$transfer->description}",
            'transfer',
            $transfer->id,
            $transfer->created_by
        );

        // Record incoming to destination wallet
        $transactions[] = $this->recordTransaction(
            $transfer->toWallet,
            'transfer_in',
            $transfer->amount,
            "Transfer from {$transfer->fromWallet->name}: {$transfer->description}",
            'transfer',
            $transfer->id,
            $transfer->created_by
        );

        return $transactions;
    }

    /**
     * Record savings contribution (money moving from wallet to savings goal).
     */
    public function recordSavingsContribution($contribution): WalletTransaction
    {
        return $this->recordTransaction(
            $contribution->fromWallet,
            'savings_contribution',
            $contribution->amount,
            "Savings contribution to {$contribution->goal->name}",
            'savings_contribution',
            $contribution->id,
            $contribution->created_by
        );
    }

    /**
     * Record savings allocation (money moving from savings goal back to budget/wallet).
     */
    public function recordSavingsAllocation($allocation): array
    {
        $transactions = [];

        // Record allocation from savings goal (this would be handled by SavingsGoal logic)
        // For now, just record the wallet receiving the funds
        $transactions[] = $this->recordTransaction(
            $allocation->budget->family->mainWallet(),
            'savings_allocation',
            $allocation->amount,
            "Savings allocation from {$allocation->goal->name} to budget {$allocation->budget->name}",
            'savings_budget_allocation',
            $allocation->id,
            $allocation->created_by
        );

        return $transactions;
    }

    /**
     * Get transaction history for a wallet.
     */
    public function getTransactionHistory(Wallet $wallet, $startDate = null, $endDate = null)
    {
        $query = $wallet->transactions()->with('creator')->orderBy('created_at', 'desc');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Recalculate all balances for a wallet (useful for data repair).
     */
    public function recalculateBalances(Wallet $wallet): void
    {
        DB::transaction(function () use ($wallet) {
            $balance = $wallet->initial_balance;
            $transactions = $wallet->transactions()->orderBy('created_at')->get();

            foreach ($transactions as $transaction) {
                $balance = $this->calculateNewBalance($balance, $transaction->transaction_type, $transaction->amount);
                $transaction->update(['balance_after' => $balance]);
            }
        });
    }
}