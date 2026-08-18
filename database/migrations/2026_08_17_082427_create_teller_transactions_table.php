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
        Schema::create('teller_transactions', function (Blueprint $table) {
            $table->id();

            $table->string('transaction_no')->unique();

            $table->foreignId('teller_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('member_id')
                ->nullable()
                ->constrained('members')
                ->nullOnDelete();

            $table->enum('type', [
                'receive',
                'payment'
            ]);

            $table->decimal('amount', 15, 2);

            $table->string('purpose')->nullable();

            $table->text('description')->nullable();

            $table->date('transaction_date');

            $table->enum('status', [
                'pending',
                'completed',
                'cancelled'
            ])->default('completed');

            $table->foreignId('finance_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teller_transactions');
    }
};
