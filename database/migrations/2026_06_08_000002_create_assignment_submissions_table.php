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
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_component_id')->constrained()->cascadeOnDelete();
            $table->enum('submission_type', ['url', 'file']);
            $table->string('url')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamp('submitted_at');
            $table->unsignedSmallInteger('days_late')->default(0);
            $table->decimal('late_penalty', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'grade_component_id']);
            $table->index(['grade_component_id', 'submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
