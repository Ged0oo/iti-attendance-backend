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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_component_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_group_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('raw_score', 8, 2);
            $table->decimal('normalized_score', 8, 2);
            $table->foreignId('graded_by')->constrained('users')->cascadeOnDelete();
            $table->decimal('override_value', 8, 2)->nullable();
            $table->text('override_note')->nullable();
            $table->foreignId('overridden_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('overridden_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'grade_component_id']);
            $table->index(['grade_component_id', 'lab_group_id']);
            $table->index('graded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
