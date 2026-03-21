<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question', 500);
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('notification_page_contents', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('contact_title', 255);
            $table->text('contact_body');
            $table->string('contact_link_url', 2048)->nullable();
            $table->string('contact_link_label', 255)->nullable();
            $table->text('dnd_intro');
            $table->string('dnd_learn_more_url', 2048)->nullable();
            $table->string('dnd_learn_more_label', 255)->nullable();
            $table->timestamps();
        });

        $now = now();
        $base = rtrim((string) config('app.url', 'http://localhost'), '/');
        DB::table('notification_page_contents')->insert([
            'id' => 1,
            'contact_title' => 'Contact support',
            'contact_body' => 'Need assistance with alerts or delivery issues? Contact our support team for prompt, personalised help.',
            'contact_link_url' => $base.'/#contact',
            'contact_link_label' => 'Contact support',
            'dnd_intro' => 'Activate “Do not disturb” to temporarily silence all notifications and focus without interruptions during specified hours or tasks.',
            'dnd_learn_more_url' => $base.'/#contact',
            'dnd_learn_more_label' => 'Learn more',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $faqs = [
            ['How are notification emails batched?', 'FamLedger groups low‑priority notifications into summary emails to avoid inbox noise, while critical alerts (like failed payments or budget breaches) are sent immediately.', 10],
            ['Can I disable all notifications temporarily?', 'Use the “Do not disturb” tab to pause all notifications for a period of time without changing your saved preferences.', 20],
            ['Do notification settings apply per family or per account?', 'Most settings are per account, but some alerts (like family budget thresholds) apply only to the currently active family.', 30],
            ['Will changes here affect existing email rules in my inbox?', 'No. FamLedger only controls what we send; any filters or rules in your email client will continue to work as you configured them.', 40],
            ['Can owners enforce minimum alerts for all members?', 'Primary owners can require certain high‑risk alerts (for example, large withdrawals) to remain enabled for all members.', 50],
            ['Where can I see a history of notifications sent?', 'The audit log (under Settings → Audit log) will gradually surface more notification activity as that feature is expanded.', 60],
        ];
        $sort = 0;
        foreach ($faqs as [$q, $a, $_]) {
            DB::table('notification_faqs')->insert([
                'question' => $q,
                'answer' => $a,
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
        Schema::dropIfExists('notification_faqs');
        Schema::dropIfExists('notification_page_contents');
    }
};
