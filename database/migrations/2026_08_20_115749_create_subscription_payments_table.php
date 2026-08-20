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
         Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();

            $table->string('payment_no',50)->unique();

            $table->foreignId('member_id')
                ->constrained('members')
                ->cascadeOnDelete();

            $table->foreignId('subscription_due_id')
                ->constrained('subscription_dues')
                ->cascadeOnDelete();

            $table->decimal('amount',15,2);

            $table->enum('payment_method',[
                'cash',
                'bank',
                'mobile_banking',
                'online'
            ]);

            $table->string(
                'transaction_reference',
                255
            )->nullable();

            $table->enum('status',[
                'pending',
                'verified',
                'rejected'
            ])->default('pending');

            $table->dateTime('paid_at');

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('verified_at')
                ->nullable();

            $table->text('verification_note')
                ->nullable();

            $table->foreignId('finance_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index(['subscription_due_id', 'status']);
            $table->index(['status', 'paid_at']);
            $table->index(['payment_method', 'status']);
            $table->index('transaction_reference');
            $table->index('finance_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
