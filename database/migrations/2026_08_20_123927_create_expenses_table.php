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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no', 50)->unique();

            $table->foreignId('expense_account_id')
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->foreignId('payment_account_id')
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->string('payee', 150)->nullable();
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
                'posted',
                'cancelled',
            ])->default('posted');

            $table->timestamps();

            $table->index(
    ['status','expense_date'],
    'expenses_status_date_idx'
);

$table->index(
    ['expense_account_id','expense_date'],
    'expenses_expense_account_date_idx'
);

$table->index(
    ['payment_account_id','expense_date'],
    'expenses_payment_account_date_idx'
);

$table->index(
    'finance_transaction_id',
    'expenses_finance_tx_idx'
);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
