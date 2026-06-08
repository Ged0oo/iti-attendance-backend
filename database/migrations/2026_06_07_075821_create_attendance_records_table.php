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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('session_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('track_id')->constrained()->onDelete('cascade');

            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('left_at')->nullable();

            $table->enum('status', ['present', 'absent', 'excused'])->default('absent');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['session_id', 'student_id', 'track_id'], 'att_session_student_track_unique');
            $table->index(['session_id', 'status'], 'att_session_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
