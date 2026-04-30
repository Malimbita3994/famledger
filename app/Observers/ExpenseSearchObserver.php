<?php

namespace App\Observers;

use App\Jobs\SyncTransactionToElasticsearchJob;
use App\Models\Expense;

class ExpenseSearchObserver
{
    public function saved(Expense $expense): void
    {
        SyncTransactionToElasticsearchJob::dispatch('sync', 'expense', $expense->id);
    }

    public function deleted(Expense $expense): void
    {
        SyncTransactionToElasticsearchJob::dispatch('delete', 'expense', $expense->id);
    }
}
