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
        Schema::table('approval_requests',function(Blueprint $table){
            $table->unsignedTinyInteger('current_step')->default(1)->after('status');
            $table->unsignedTinyInteger('total_steps')->default(1)->after('current_step');
            $table->timestamp('completed_at')->nullable()->after('total_steps');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_requests',function(Blueprint $table){
            $table->dropColumn(['current_step','total_steps','completed_at']);
        });
    }
};
