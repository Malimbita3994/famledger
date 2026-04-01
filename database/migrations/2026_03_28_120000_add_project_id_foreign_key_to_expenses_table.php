<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Link expenses to projects with referential integrity (orphans cleared, then FK with null on delete).
     */
    public function up(): void
    {
        $invalidExpenseIds = DB::table('expenses')
            ->whereNotNull('project_id')
            ->whereNotIn('project_id', DB::table('projects')->select('id'))
            ->pluck('id');

        if ($invalidExpenseIds->isNotEmpty()) {
            DB::table('expenses')
                ->whereIn('id', $invalidExpenseIds->all())
                ->update(['project_id' => null]);
        }

        if ($this->expensesProjectForeignKeyExists()) {
            return;
        }

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! $this->expensesProjectForeignKeyExists()) {
            return;
        }

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });
    }

    private function expensesProjectForeignKeyExists(): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $row = DB::selectOne(
                'SELECT COUNT(*) AS c FROM information_schema.TABLE_CONSTRAINTS
                 WHERE CONSTRAINT_SCHEMA = DATABASE()
                 AND TABLE_NAME = ?
                 AND CONSTRAINT_TYPE = ?
                 AND CONSTRAINT_NAME = ?',
                ['expenses', 'FOREIGN KEY', 'expenses_project_id_foreign']
            );

            return $row && (int) $row->c > 0;
        }

        if ($driver === 'sqlite') {
            $rows = DB::select('PRAGMA foreign_key_list(expenses)');
            foreach ($rows as $row) {
                if (($row->table ?? '') === 'projects' && ($row->from ?? '') === 'project_id') {
                    return true;
                }
            }

            return false;
        }

        return false;
    }
};
