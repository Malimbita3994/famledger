<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_support_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('body');
            $table->string('link_url', 2048)->nullable();
            $table->string('link_label', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $page = DB::table('notification_page_contents')->where('id', 1)->first();
        if ($page) {
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

        Schema::dropIfExists('notification_support_contacts');
    }
};
