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
        Schema::create('tour_participants',function(Blueprint $table){
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->enum('status',['registered','confirmed','cancelled','attended','absent'])->default('registered');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tour_id','member_id']);
            $table->index(['tour_id','status']);
            $table->index(['member_id','status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_participants');
    }
};
