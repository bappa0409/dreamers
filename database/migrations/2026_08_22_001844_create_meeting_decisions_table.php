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
         Schema::create('meeting_decisions',function(Blueprint $table){
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignId('meeting_agenda_id')->nullable()->constrained('meeting_agendas')->nullOnDelete();
            $table->string('decision_no',40)->unique();
            $table->string('title');
            $table->text('decision');
            $table->enum('result',['approved','rejected','deferred','noted'])->default('approved');
            $table->unsignedInteger('votes_for')->default(0);
            $table->unsignedInteger('votes_against')->default(0);
            $table->unsignedInteger('votes_abstain')->default(0);
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->enum('status',['pending','in_progress','completed','cancelled'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_notes')->nullable();
            $table->timestamps();

            $table->index(['meeting_id','status']);
            $table->index(['responsible_user_id','status']);
            $table->index(['status','due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_decisions');
    }
};
