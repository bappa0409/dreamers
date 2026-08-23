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
        Schema::create('committee_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_term_id')->constrained()->restrictOnDelete();
            $table->foreignId('committee_position_id')->constrained()->restrictOnDelete();
            $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->enum('appointment_method',['appointed','elected','replacement'])->default('appointed');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status',['active','resigned','removed','completed'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['committee_term_id','status']);
            $table->index(['committee_position_id','status']);
            $table->index(['member_id','status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_members');
    }
};
