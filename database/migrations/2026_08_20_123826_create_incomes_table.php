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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->string('income_no', 50)->unique();

            $table->foreignId('member_id')
                ->nullable()
                ->constrained('members')
                ->nullOnDelete();

            $table->foreignId('income_account_id')
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->foreignId('receive_account_id')
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->decimal('amount', 15, 2);
            $table->date('income_date');
            $table->string('reference', 150)->nullable();
            $table->text('description')->nullable();
            $table->string('attachment')->nullable();

            $table->foreignId('finance_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('status', [
                'pending_approval',
                'posted',
                'rejected',
                'cancelled',
            ])->default('pending_approval');

            $table->timestamps();

            $table->index(
                ['status', 'income_date'],
                'incomes_status_date_idx'
            );

            $table->index(
                ['income_account_id', 'income_date'],
                'incomes_income_account_date_idx'
            );

            $table->index(
                ['receive_account_id', 'income_date'],
                'incomes_receive_account_date_idx'
            );

            $table->index(
                ['member_id', 'income_date'],
                'incomes_member_date_idx'
            );

            $table->index(
                'finance_transaction_id',
                'incomes_finance_tx_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
