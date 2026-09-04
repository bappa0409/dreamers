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
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('member_code')->unique();
            $table->string('father_or_husband_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('nid_or_birth_reg_no')->nullable();
            $table->text('address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('profession')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->date('joining_date')->nullable();

            $table->enum('status', [
                'pending',
                'active',
                'inactive',
                'suspended',
                'rejected',
                'exited',
                'deceased',
            ])->default('pending');

            $table->string('profile_photo')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status','member_code'],'members_status_code_idx');
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                    $table->fullText(['member_code','phone','city','district']);
                }
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
