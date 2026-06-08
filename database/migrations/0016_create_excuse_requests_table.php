<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excuse_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            
            // TODO: uncomment when M4 merges
            // $table->foreignId('attendance_record_id')->constrained('attendance_records')->cascadeOnDelete();
            $table->unsignedBigInteger('attendance_record_id');
            
            $table->enum('status', ['requested', 'approved', 'rejected'])->default('requested');
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excuse_requests');
    }
};
