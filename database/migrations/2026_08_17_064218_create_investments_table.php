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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->string('investment_no')->unique();

            $table->foreignId('member_id')
                ->constrained('members')
                ->cascadeOnDelete();

            $table->foreignId('payment_account_id')
                ->nullable()
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('expected_return', 15, 2)->default(0);
            $table->date('investment_date');
            $table->date('maturity_date')->nullable();

            $table->enum('status', [
                'pending',
                'active',
                'completed',
                'cancelled',
            ])->default('pending');

            $table->foreignId('finance_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('investment_date');
            $table->index('maturity_date');
            $table->index('status');
            $table->index(['member_id', 'status']);
            $table->index(['payment_account_id', 'investment_date']);
            $table->index(['status', 'maturity_date']);
            $table->index('finance_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
