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
        Schema::create('lands', function (Blueprint $table) {
             $table->id();
            $table->string('land_code')->unique();
            $table->string('title');

            $table->text('description')->nullable();

            $table->string('district')->nullable();
            $table->string('upazila')->nullable();
            $table->string('mouza')->nullable();

            $table->string('khatian_no')->nullable();
            $table->string('dag_no')->nullable();

            $table->string('deed_no',150)->nullable();
            $table->string('registration_no',150)->nullable();

            $table->decimal('land_area',12,4)->nullable();

            $table->enum('area_unit',[
                'decimal',
                'katha',
                'bigha',
                'acre',
                'sqft',
                'hectare',
            ])->default('decimal');

            $table->decimal('purchase_price',15,2)->default(0);
            $table->decimal('current_value',15,2)->nullable();

            $table->date('purchase_date')->nullable();

            $table->foreignId('payment_account_id')
                ->nullable()
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->foreignId('finance_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->string('seller_name')->nullable();
            $table->string('seller_phone',30)->nullable();

            $table->enum('status',[
                'planned',
                'negotiating',
                'purchased',
                'sold',
                'cancelled',
            ])->default('planned');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['status','purchase_date']);
            $table->index(['district','upazila']);
            $table->index(['mouza','khatian_no','dag_no']);
            $table->index('registration_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lands');
    }
};
