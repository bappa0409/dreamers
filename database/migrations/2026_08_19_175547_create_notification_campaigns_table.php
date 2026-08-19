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
        Schema::create('notification_campaigns',function(Blueprint $table){
            $table->id();
            $table->string('title',200);
            $table->text('message');
            $table->string('type',30)->default('info');
            $table->string('audience_type',30)->index();
            $table->json('audience_data')->nullable();
            $table->string('action_url')->nullable();
            $table->string('status',30)->default('pending')->index();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['status','created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_campaigns');
    }
};
