<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The temporary attendance setup still creates this table. While that is
        // the case we skip, so nothing collides. Once that create is removed this
        // becomes the real owner of the engagements table.
        if (Schema::hasTable('engagements')) {
            return;
        }

        Schema::create('engagements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_id')->constrained()->cascadeOnDelete();
            // instructor_id points at users.id (the teaching seat)
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->string('type'); // lecture, lab or business_session
            $table->date('date_range_start');
            $table->date('date_range_end');
            $table->integer('scheduled_hours');
            $table->string('status')->default('scheduled');
            $table->timestamps();

            // course_id is added later, after the courses table exists
            $table->index(['cohort_id', 'instructor_id', 'type']);
        });
    }

    public function down(): void
    {
        // No drop here. The table may have been created by the temporary setup,
        // so teardown stays with whoever owns the swap.
    }
};
