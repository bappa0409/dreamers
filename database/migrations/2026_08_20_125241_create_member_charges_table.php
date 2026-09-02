<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_charges',function(Blueprint $table){
            $table->id();

            $table->string('charge_no',50)->unique();

            $table->foreignId('member_id')
                ->constrained('members')
                ->restrictOnDelete();

            $table->foreignId('income_account_id')
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->decimal('amount',15,2);
            $table->decimal('paid_amount',15,2)->default(0);

            $table->date('charge_date');
            $table->date('due_date')->nullable();

            $table->string('charge_type',50);
            $table->string('reference',150)->nullable();
            $table->text('description')->nullable();

            $table->enum('status', [
                'pending_approval',
                'rejected',
                'unpaid',
                'partial',
                'paid',
                'waived',
                'cancelled',
            ])->default('pending_approval');

            $table->foreignId('finance_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Optimized Indexes
            |--------------------------------------------------------------------------
            */

            // Member portal / member charge history
            $table->index(
                ['member_id','status','charge_date'],
                'member_charges_member_status_date_idx'
            );

            // Main admin filter: status + date range
            $table->index(
                ['status','charge_date'],
                'member_charges_status_date_idx'
            );

            // Overdue/due processing
            $table->index(
                ['status','due_date'],
                'member_charges_status_due_idx'
            );

            // Charge type filtering/reporting
            $table->index(
                ['charge_type','status','charge_date'],
                'member_charges_type_status_date_idx'
            );

            // Income account reporting
            $table->index(
                ['income_account_id','charge_date'],
                'member_charges_income_date_idx'
            );

            // Finance transaction relation/lookups
            $table->index(
                'finance_transaction_id',
                'member_charges_finance_tx_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_charges');
    }
};