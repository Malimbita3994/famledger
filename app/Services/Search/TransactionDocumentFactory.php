<?php

namespace App\Services\Search;

use App\Models\Expense;
use App\Models\Income;
use Illuminate\Support\Str;

/**
 * Maps Income / Expense models to Elasticsearch documents.
 * household_id mirrors family_id for multi-tenant isolation (same value).
 */
class TransactionDocumentFactory
{
    public function documentIdForIncome(int $id): string
    {
        return 'income_'.$id;
    }

    public function documentIdForExpense(int $id): string
    {
        return 'expense_'.$id;
    }

    /**
     * @return array<string, mixed>
     */
    public function fromIncome(Income $income): array
    {
        $income->loadMissing(['category', 'receivedBy']);

        $categoryName = $income->category?->name ?? '';
        $title = $income->source !== null && $income->source !== ''
            ? (string) $income->source
            : ($categoryName !== '' ? $categoryName : 'Income');

        $person = $income->receivedBy?->name ?? '';

        $fid = (string) $income->family_id;
        $received = $income->received_date?->format('Y-m-d') ?? $income->created_at->format('Y-m-d');
        $created = $income->created_at->toIso8601String();

        return [
            'id' => $this->documentIdForIncome($income->id),
            'type' => 'income',
            'title' => $title,
            'description' => (string) ($income->notes ?? ''),
            'amount' => (float) $income->amount,
            'currency_code' => (string) ($income->currency_code ?? ''),
            'category' => $categoryName,
            'category_id' => (int) $income->category_id,
            'person' => $person,
            'person_keyword' => $person,
            'date' => $received,
            'tags' => [],
            'household_id' => $fid,
            'family_id' => $fid,
            'record_id' => $income->id,
            'created_at' => $created,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function fromExpense(Expense $expense): array
    {
        $expense->loadMissing(['category', 'paidBy']);

        $categoryName = $expense->category?->name ?? '';
        $title = $expense->description !== null && $expense->description !== ''
            ? (string) $expense->description
            : ($expense->merchant !== null && $expense->merchant !== ''
                ? (string) $expense->merchant
                : ($categoryName !== '' ? $categoryName : 'Expense'));

        $person = $expense->paidBy?->name ?? '';

        $tags = [];
        if (is_string($expense->subcategory) && $expense->subcategory !== '') {
            $tags[] = $expense->subcategory;
        }

        $fid = (string) $expense->family_id;
        $day = $expense->expense_date?->format('Y-m-d') ?? $expense->created_at->format('Y-m-d');
        $created = $expense->created_at->toIso8601String();

        return [
            'id' => $this->documentIdForExpense($expense->id),
            'type' => 'expense',
            'title' => $title,
            'description' => trim(Str::limit((string) ($expense->merchant ?? '').' '.(string) ($expense->reference ?? ''), 2000)),
            'amount' => (float) $expense->amount,
            'currency_code' => (string) ($expense->currency_code ?? ''),
            'category' => $categoryName,
            'category_id' => (int) $expense->category_id,
            'person' => $person,
            'person_keyword' => $person,
            'date' => $day,
            'tags' => $tags,
            'household_id' => $fid,
            'family_id' => $fid,
            'record_id' => $expense->id,
            'created_at' => $created,
        ];
    }
}
