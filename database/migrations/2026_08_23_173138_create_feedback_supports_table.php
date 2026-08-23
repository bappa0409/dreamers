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
        Schema::create('feedback_supports',function(Blueprint $table){
            $table->id();
            $table->string('ticket_no',50)->unique();

            $table->foreignId('member_id')
                ->constrained('members')
                ->restrictOnDelete();

            $table->foreignId('feedback_support_category_id')
                ->constrained('feedback_support_categories')
                ->restrictOnDelete();

            $table->enum('type',[
                'feedback',
                'support_request',
                'complaint',
                'suggestion',
                'service_issue',
                'other'
            ])->default('support_request');

            $table->string('subject',200);
            $table->text('description');

            $table->enum('priority',[
                'low',
                'normal',
                'high',
                'urgent'
            ])->default('normal');

            $table->enum('status',[
                'submitted',
                'under_review',
                'assigned',
                'in_progress',
                'resolved',
                'closed',
                'rejected',
                'cancelled'
            ])->default('submitted');

            $table->boolean('is_confidential')->default(false);

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('assigned_at')->nullable();

            $table->text('resolution')->nullable();

            $table->foreignId('resolved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('resolved_at')->nullable();

            $table->foreignId('closed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('closed_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['member_id','status'],'feedback_support_member_status_idx');
            $table->index(['assigned_to','status'],'feedback_support_assigned_status_idx');
            $table->index(['status','priority'],'feedback_support_status_priority_idx');
            $table->index(['type','status'],'feedback_support_type_status_idx');
            $table->index(['feedback_support_category_id','status'],'feedback_support_category_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_supports');
    }
};
