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
        Schema::create('election_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->restrictOnDelete();
            $table->foreignId('committee_position_id')->constrained()->restrictOnDelete();
            $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('votes')->default(0);
            $table->enum('status',['candidate','elected','not_elected','withdrawn'])->default('candidate');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['election_id','committee_position_id','member_id'],'election_candidate_unique');
            $table->index(['election_id','committee_position_id','status'],'election_result_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('election_candidates');
    }
};
