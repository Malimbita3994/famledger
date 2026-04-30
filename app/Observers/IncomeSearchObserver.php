<?php

namespace App\Observers;

use App\Jobs\SyncTransactionToElasticsearchJob;
use App\Models\Income;

class IncomeSearchObserver
{
    public function saved(Income $income): void
    {
        SyncTransactionToElasticsearchJob::dispatch('sync', 'income', $income->id);
    }

    public function deleted(Income $income): void
    {
        SyncTransactionToElasticsearchJob::dispatch('delete', 'income', $income->id);
    }
}
