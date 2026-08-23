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
        Schema::create('member_exit_nominee_allocations',function(Blueprint $table){
            $table->id();

            $table->foreignId('member_exit_id')
                ->constrained('member_exits')
                ->restrictOnDelete();

            $table->foreignId('member_nominee_id')
                ->constrained('member_nominees')
                ->restrictOnDelete();

            $table->decimal('allocation_percentage',5,2);
            $table->decimal('amount',15,2);

            $table->timestamps();

            $table->unique(
                ['member_exit_id','member_nominee_id'],
                'exit_nominee_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_exit_nominee_allocations');
    }
};
