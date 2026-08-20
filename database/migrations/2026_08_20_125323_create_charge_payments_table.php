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
        Schema::create('charge_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no', 50)->unique();

            $table->foreignId('member_charge_id')
                ->constrained('member_charges')
                ->restrictOnDelete();

            $table->decimal('amount', 15, 2);

            $table->foreignId('receive_account_id')
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->date('payment_date');
            $table->string('payment_method', 30)->nullable();
            $table->string('reference', 150)->nullable();
            $table->text('description')->nullable();

            $table->enum('status', [
                'posted',
                'cancelled',
            ])->default('posted');

            $table->foreignId('finance_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('payment_date');
            $table->index('status');
            $table->index(['member_charge_id', 'status']);
            $table->index(['receive_account_id', 'payment_date']);
            $table->index(['status', 'payment_date']);
            $table->index(['finance_transaction_id', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charge_payments');
    }
};
