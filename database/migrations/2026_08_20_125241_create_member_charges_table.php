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
        Schema::create('member_charges', function (Blueprint $table) {
            $table->id();
            $table->string('charge_no', 50)->unique();

            $table->foreignId('member_id')
                ->constrained('members')
                ->restrictOnDelete();

            $table->foreignId('income_account_id')
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->decimal('amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);

            $table->date('charge_date');
            $table->date('due_date')->nullable();

            $table->string('charge_type', 50);
            $table->string('reference', 150)->nullable();
            $table->text('description')->nullable();

            $table->enum('status', [
                'unpaid',
                'partial',
                'paid',
                'waived',
                'cancelled',
            ])->default('unpaid');

            $table->foreignId('finance_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('charge_date');
            $table->index('due_date');
            $table->index('status');
            $table->index(['member_id', 'status']);
            $table->index(['income_account_id', 'charge_date']);
            $table->index(['charge_type', 'status']);
            $table->index(['status', 'due_date']);
            $table->index(['finance_transaction_id', 'charge_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_charges');
    }
};
