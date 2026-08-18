<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('approval_requests', function (Blueprint $table) {

            if (!Schema::hasColumn('approval_requests', 'cancelled_by')) {

                $table->foreignId('cancelled_by')
                    ->nullable()
                    ->after('reason')
                    ->constrained('users')
                    ->nullOnDelete();
            }


            if (!Schema::hasColumn('approval_requests', 'cancelled_at')) {

                $table->timestamp('cancelled_at')
                    ->nullable()
                    ->after('cancelled_by');
            }


            if (!Schema::hasColumn('approval_requests', 'cancellation_reason')) {

                $table->text('cancellation_reason')
                    ->nullable()
                    ->after('cancelled_at');
            }
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_requests', function (Blueprint $table) {

            if (Schema::hasColumn('approval_requests', 'cancelled_by')) {

                $table->dropForeign(['cancelled_by']);

                $table->dropColumn('cancelled_by');
            }


            if (Schema::hasColumn('approval_requests', 'cancelled_at')) {

                $table->dropColumn('cancelled_at');
            }


            if (Schema::hasColumn('approval_requests', 'cancellation_reason')) {

                $table->dropColumn('cancellation_reason');
            }
        });
    }
};