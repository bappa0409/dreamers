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
        Schema::create('subscription_dues',function(Blueprint $table){
            $table->id();

            $table->foreignId('member_subscription_id')
    ->constrained('member_subscriptions')
    ->restrictOnDelete();

            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');

            $table->decimal('base_amount',15,2)->default(0);
            $table->unsignedInteger('share_count')->default(1);
            $table->decimal('fine_amount',15,2)->default(0);

            $table->decimal('amount',15,2);
            $table->decimal('paid_amount',15,2)->default(0);

            $table->date('due_date');
            $table->date('fine_applied_at')->nullable();

            $table->enum('status',[
                'unpaid',
                'partial',
                'paid',
                'overdue',
                'waived'
            ])->default('unpaid');

            $table->foreignId('finance_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                [
                    'member_subscription_id',
                    'year',
                    'month'
                ],
                'subscription_dues_period_unique'
            );

            $table->index(['year','month']);
            $table->index(['status','due_date']);
            $table->index('fine_applied_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_dues');
    }
};
