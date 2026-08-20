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
        Schema::create('approval_workflow_steps',function(Blueprint $table){
            $table->id();
            $table->foreignId('approval_workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->unsignedTinyInteger('step_no');
            $table->foreignId('approver_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['approval_workflow_id','step_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_workflow_steps');
    }
};
