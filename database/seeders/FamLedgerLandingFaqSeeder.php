<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Replaces all rows in notification_faqs with the default customer FAQ set.
 * Run manually: php artisan db:seed --class=FamLedgerLandingFaqSeeder
 */
class FamLedgerLandingFaqSeeder extends Seeder
{
    public function run(): void
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
                'group_label' => $row['group'],
                'sort_order' => $sort,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $sort += 10;
        }
    }
}
