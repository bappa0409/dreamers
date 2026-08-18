<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_roles')) {
            return;
        }

        if (!Schema::hasColumn('users', 'role_id')) {
            return;
        }

        DB::table('users')
            ->whereNotNull('role_id')
            ->select('id', 'role_id')
            ->orderBy('id')
            ->chunkById(500, function ($users) {

                $rows = [];

                $now = now();

                foreach ($users as $user) {
                    $rows[] = [
                        'user_id' => $user->id,
                        'role_id' => $user->role_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($rows)) {
                    DB::table('user_roles')->insertOrIgnore($rows);
                }
            });
    }

    public function down(): void
    {
        /*
         * Intentionally left empty.
         *
         * We don't delete user_roles here because
         * those assignments may have been changed
         * after the migration ran.
         */
    }
};
