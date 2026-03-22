<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Replace FAQ rows with a full customer-facing set for the landing page
     * and Settings → Notifications → FAQ (same table).
     */
    public function up(): void
    {
        /** @var list<array{group: string, question: string, answer: string}> $rows */
        $rows = require database_path('data/famledger_customer_faqs.php');

        DB::table('notification_faqs')->truncate();

        $now = now();
        $sort = 0;

        foreach ($rows as $row) {
            DB::table('notification_faqs')->insert([
                'question' => $row['question'],
                'answer' => $row['answer'],
                'sort_order' => $sort,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $sort += 10;
        }
    }

    public function down(): void
    {
        // Intentionally empty: prior FAQ content is not restored.
    }
};
