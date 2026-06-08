<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // TODO: uncomment when M2 merges
            // $table->foreignId('cohort_id')->constrained('cohorts')->cascadeOnDelete();
            $table->unsignedBigInteger('cohort_id');
            
            // TODO: uncomment when M3 merges
            // $table->foreignId('lab_group_id')->nullable()->constrained('lab_groups')->nullOnDelete();
            $table->unsignedBigInteger('lab_group_id')->nullable();
            
            $table->boolean('is_at_risk')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
