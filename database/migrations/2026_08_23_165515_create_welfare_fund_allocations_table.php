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
        Schema::create('welfare_fund_allocations',function(Blueprint $table){
            $table->id();

            $table->foreignId('welfare_fund_id')
                ->constrained('welfare_funds')
                ->restrictOnDelete();

            $table->decimal('amount',15,2);

            $table->date('allocation_date');

            $table->enum('source_type',[
                'association_fund',
                'donation',
                'special_allocation',
                'other'
            ])->default('association_fund');

            $table->string('source_reference',150)->nullable();
            $table->text('description')->nullable();

            /*
             * Optional link to existing finance transaction.
             * Example: donation income already posted through Income module.
             */
            $table->foreignId('source_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->restrictOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['welfare_fund_id','allocation_date'],
                'welfare_allocations_fund_date_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welfare_fund_allocations');
    }
};
