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
        Schema::create('approval_workflows',function(Blueprint $table){
            $table->id();
            $table->string('module',100);
            $table->string('action',50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['module','action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_workflows');
    }
};
