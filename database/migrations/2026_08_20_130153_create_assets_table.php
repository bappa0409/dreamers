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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 50)->unique();
            $table->string('name', 150);
            $table->string('category', 100)->nullable();

            $table->foreignId('asset_account_id')
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->foreignId('payment_account_id')
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->decimal('purchase_cost', 15, 2);
            $table->date('purchase_date');
            $table->string('vendor', 150)->nullable();
            $table->string('reference', 150)->nullable();
            $table->string('location', 150)->nullable();
            $table->string('serial_no', 150)->nullable();
            $table->string('attachment')->nullable();

            $table->unsignedInteger('useful_life_months')->nullable();
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);

            $table->enum('status', [
                'active',
                'sold',
                'disposed',
                'cancelled',
            ])->default('active');

            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_amount', 15, 2)->nullable();
            $table->text('disposal_note')->nullable();

            $table->foreignId('finance_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->foreignId('disposal_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('purchase_date');
            $table->index('status');
            $table->index(['asset_account_id', 'status']);
            $table->index(['payment_account_id', 'purchase_date']);
            $table->index(['category', 'status']);
            $table->index(['location', 'status']);
            $table->index(['vendor', 'purchase_date']);
            $table->index(['status', 'disposal_date']);
            $table->index(['finance_transaction_id', 'purchase_date']);
            $table->index('disposal_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
