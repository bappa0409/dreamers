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
        Schema::create('member_nominees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')
                ->constrained('members')
                ->restrictOnDelete();
                
            $table->string('name',150);
            $table->string('relationship',80);
            $table->string('father_or_husband_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('phone',30)->nullable();

            $table->enum('identity_type',[
                'nid',
                'birth_certificate',
                'passport',
                'other'
            ])->nullable();

            $table->string('identity_number',100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('profession')->nullable();
            $table->text('address')->nullable();
            $table->text('permanent_address')->nullable();

            $table->decimal('allocation_percentage',5,2);
            $table->unsignedSmallInteger('priority')->default(1);

            $table->boolean('is_active')->default(true);

            $table->enum('verification_status',[
                'unverified',
                'pending',
                'verified',
                'rejected'
            ])->default('unverified');

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();
            $table->text('verification_note')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['member_id','is_active'],
                'nominees_member_active_idx'
            );

            $table->index(
                ['member_id','verification_status'],
                'nominees_member_verification_idx'
            );

            $table->index(
                ['member_id','priority'],
                'nominees_member_priority_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_nominees');
    }
};
