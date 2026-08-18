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
        Schema::create('teller_closings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teller_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->date('closing_date');

            $table->decimal('opening_balance', 15, 2)->default(0);

            $table->decimal('total_received', 15, 2)->default(0);

            $table->decimal('total_paid', 15, 2)->default(0);

            $table->decimal('expected_balance', 15, 2)->default(0);

            $table->decimal('actual_balance', 15, 2)->nullable();

            $table->decimal('difference', 15, 2)->nullable();

            $table->enum('status', [
                'open',
                'closed',
                'reopened'
            ])->default('open');

            $table->text('notes')->nullable();

            $table->timestamp('closed_at')->nullable();

            $table->foreignId('closed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique([
                'teller_id',
                'closing_date'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teller_closings');
    }
};
