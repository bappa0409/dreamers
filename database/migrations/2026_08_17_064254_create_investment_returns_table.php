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
        Schema::create('investment_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')
    ->constrained('investments')
    ->restrictOnDelete();

            $table->enum('return_type', [
                'income',
                'principal',
            ])->default('income');

            $table->foreignId('receive_account_id')
                ->nullable()
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->decimal('amount', 15, 2);
            $table->date('return_date');
            $table->text('description')->nullable();

            $table->enum('status', [
                'pending',
                'paid',
                'cancelled',
            ])->default('pending');

            $table->foreignId('finance_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['investment_id', 'status']);
            $table->index(['investment_id', 'return_type']);
            $table->index(['return_type', 'status']);
            $table->index(['receive_account_id', 'return_date']);
            $table->index(['investment_id', 'return_date']);
            $table->index('finance_transaction_id');
            $table->index(['investment_id','status','return_type'],'investment_returns_summary_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_returns');
    }
};
