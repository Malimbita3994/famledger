<?php

namespace App\Console\Commands;

use App\Jobs\SyncTransactionToElasticsearchJob;
use App\Models\Expense;
use App\Models\Income;
use Illuminate\Console\Command;

class SearchReindexTransactionsCommand extends Command
{
    protected $signature = 'search:reindex {--chunk=200 : Chunk size for dispatching jobs}';

    protected $description = 'Dispatch queue jobs to reindex all incomes and expenses into Elasticsearch';

    public function handle(): int
    {
        if (! config('elasticsearch.enabled')) {
            $this->warn('ELASTICSEARCH_ENABLED is false.');

            return self::FAILURE;
        }

        $chunk = max(1, (int) $this->option('chunk'));

        $incomeCount = 0;
        Income::query()->orderBy('id')->chunkById($chunk, function ($rows) use (&$incomeCount) {
            foreach ($rows as $income) {
                SyncTransactionToElasticsearchJob::dispatch('sync', 'income', $income->id);
                $incomeCount++;
            }
        });

        $expenseCount = 0;
        Expense::query()->orderBy('id')->chunkById($chunk, function ($rows) use (&$expenseCount) {
            foreach ($rows as $expense) {
                SyncTransactionToElasticsearchJob::dispatch('sync', 'expense', $expense->id);
                $expenseCount++;
            }
        });

        $this->info("Queued {$incomeCount} income and {$expenseCount} expense sync jobs. Run queue worker to process.");

        return self::SUCCESS;
    }
}
