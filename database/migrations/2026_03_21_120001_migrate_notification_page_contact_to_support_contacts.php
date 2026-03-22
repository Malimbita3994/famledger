<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Runs after notification_page_contents exists: seed support row from legacy
     * contact_* columns, then drop those columns (model uses notification_support_contacts).
     */
    public function up(): void
    {
        if (! Schema::hasTable('notification_page_contents') || ! Schema::hasTable('notification_support_contacts')) {
            return;
        }

        if (! Schema::hasColumn('notification_page_contents', 'contact_title')) {
            return;
        }

        $page = DB::table('notification_page_contents')->where('id', 1)->first();
        if ($page && ! DB::table('notification_support_contacts')->exists()) {
            DB::table('notification_support_contacts')->insert([
                'title' => $page->contact_title ?? 'Contact support',
                'body' => $page->contact_body ?? '',
                'link_url' => $page->contact_link_url,
                'link_label' => $page->contact_link_label,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('notification_page_contents', function (Blueprint $table) {
            $table->dropColumn([
                'contact_title',
                'contact_body',
                'contact_link_url',
                'contact_link_label',
            ]);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('notification_page_contents')) {
            return;
        }

        Schema::table('notification_page_contents', function (Blueprint $table) {
            $table->string('contact_title', 255)->default('');
            $table->text('contact_body')->default('');
            $table->string('contact_link_url', 2048)->nullable();
            $table->string('contact_link_label', 255)->nullable();
        });

        $first = DB::table('notification_support_contacts')->orderBy('sort_order')->orderBy('id')->first();
        if ($first) {
            DB::table('notification_page_contents')->where('id', 1)->update([
                'contact_title' => $first->title,
                'contact_body' => $first->body,
                'contact_link_url' => $first->link_url,
                'contact_link_label' => $first->link_label,
            ]);
        }
    }
};
