<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_faqs', function (Blueprint $table) {
            $table->string('group_label', 120)->nullable()->after('answer');
        });

        /** @var list<array{group: string, question: string, answer: string}> $rows */
        $rows = require database_path('data/famledger_customer_faqs.php');

        foreach ($rows as $row) {
            DB::table('notification_faqs')
                ->where('question', $row['question'])
                ->update(['group_label' => $row['group']]);
        }
    }

    public function down(): void
    {
        Schema::table('notification_faqs', function (Blueprint $table) {
            $table->dropColumn('group_label');
        });
    }
};
