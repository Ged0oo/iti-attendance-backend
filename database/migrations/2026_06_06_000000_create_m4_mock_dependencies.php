<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Mock M1's Users Table update
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student');
        });

        // 2. Mock M1/M2's Branches & Tracks
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
        });

        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained();
            $table->string('name');
            $table->string('description')->nullable();
        });

        // 3. Mock M2's Cohorts
        Schema::create('cohorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained();
            $table->string('name');
            $table->string('status');
            $table->date('start_date');
            $table->date('end_date');
        });

        // 4. Mock M3's Engagements & Class Sessions
        Schema::create('engagements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_id')->constrained();
            $table->foreignId('instructor_id')->references('id')->on('users');
            $table->string('type');
            $table->date('date_range_start');
            $table->date('date_range_end');
            $table->integer('scheduled_hours');
            $table->string('status');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->id(); // This creates the BIGINT your attendance table is expecting
            $table->foreignId('engagement_id')->constrained();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('scheduled_hours');
            $table->boolean('is_delivered');
            $table->string('qr_code');
        });

        // 5. Mock M5's Students
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('cohort_id')->constrained();
            $table->string('national_id');
            $table->boolean('is_at_risk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('engagements');
        Schema::dropIfExists('cohorts');
        Schema::dropIfExists('tracks');
        Schema::dropIfExists('branches');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};