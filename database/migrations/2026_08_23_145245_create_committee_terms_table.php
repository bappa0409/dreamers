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
        Schema::create('committee_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_id')->constrained()->restrictOnDelete();
            $table->string('name',100);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status',['draft','active','completed','cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['committee_id','name']);
            $table->index(['committee_id','status']);
            $table->index(['start_date','end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_terms');
    }
};
