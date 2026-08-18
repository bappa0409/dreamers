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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();

            $table->string('investment_no')->unique();

            $table->foreignId('member_id')
                ->constrained('members')
                ->cascadeOnDelete();

            $table->string('title');

            $table->text('description')->nullable();

            $table->decimal('amount', 15, 2);

            $table->decimal('expected_return', 15, 2)
                ->default(0);

            $table->date('investment_date');

            $table->date('maturity_date')->nullable();

            $table->enum('status', [
                'pending',
                'active',
                'completed',
                'cancelled',
            ])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
