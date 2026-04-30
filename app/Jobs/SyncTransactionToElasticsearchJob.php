<?php

namespace App\Jobs;

use App\Models\Expense;
use App\Models\Income;
use App\Services\Search\SearchService;
use App\Services\Search\TransactionDocumentFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Async index/delete for income & expense documents in Elasticsearch.
 */
class SyncTransactionToElasticsearchJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $mode,
        public string $kind,
        public int $recordId,
    ) {
        $this->onQueue('default');
    }

    public function handle(SearchService $search, TransactionDocumentFactory $factory): void
    {
        if (! $search->isEnabled()) {
            return;
        }

        if ($this->mode === 'delete') {
            $docId = $this->kind === 'income'
                ? $factory->documentIdForIncome($this->recordId)
                : $factory->documentIdForExpense($this->recordId);
            $search->deleteDocument($docId);

            return;
        }

        if ($this->kind === 'income') {
            $income = Income::query()->with(['category', 'receivedBy'])->find($this->recordId);
            if ($income) {
                $search->indexDocument(
                    $factory->documentIdForIncome($income->id),
                    $factory->fromIncome($income)
                );
            }

            return;
        }

        if ($this->kind === 'expense') {
            $expense = Expense::query()->with(['category', 'paidBy'])->find($this->recordId);
            if ($expense) {
                $search->indexDocument(
                    $factory->documentIdForExpense($expense->id),
                    $factory->fromExpense($expense)
                );
            }
        }
    }
}
