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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index();
            $table->decimal('amount', 15, 2);
            $table->unsignedTinyInteger('due_day')->default(10);
            $table->text('description')->nullable();

            $table->enum('fine_type',[
                'none',
                'fixed',
                'percentage'
            ])->default('none');

            $table->decimal('fine_value',15,2)->default(0);
            $table->unsignedTinyInteger('grace_days')->default(0);

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
