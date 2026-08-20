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
        Schema::create('member_shares',function(Blueprint $table){
            $table->id();

            $table->foreignId('member_id')
                ->constrained('members')
                ->cascadeOnDelete();

            $table->string('share_no',50)->unique();

            $table->decimal('purchase_amount',15,2);

            $table->date('acquired_date')->nullable();

            $table->enum('payment_method',[
                'cash',
                'bank',
                'mobile_banking',
                'online',
            ])->nullable();

            $table->string(
                'transaction_reference',
                255
            )->nullable();

            $table->enum('status',[
                'pending',
                'active',
                'rejected',
                'transferred',
                'cancelled',
                'retired',
            ])->default('pending');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp(
                'verified_at'
            )->nullable();

            $table->text(
                'verification_note'
            )->nullable();

            $table->foreignId(
                'finance_transaction_id'
            )
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['member_id','status']);
            $table->index('acquired_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_shares');
    }
};
