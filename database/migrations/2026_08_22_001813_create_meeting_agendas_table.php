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
        Schema::create('meeting_agendas',function(Blueprint $table){
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status',['pending','discussed','deferred','cancelled'])->default('pending');
            $table->timestamps();

            $table->index(['meeting_id','sort_order']);
            $table->index(['meeting_id','status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_agendas');
    }
};
