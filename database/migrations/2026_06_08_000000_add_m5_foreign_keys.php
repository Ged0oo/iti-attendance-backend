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
        Schema::table('students', function (Blueprint $table) {
            $table->foreign('cohort_id')->references('id')->on('cohorts')->cascadeOnDelete();
            $table->foreign('lab_group_id')->references('id')->on('lab_groups')->nullOnDelete();
        });

        Schema::table('attendance_ledger_entries', function (Blueprint $table) {
            $table->foreign('attendance_record_id')->references('id')->on('attendance_records')->nullOnDelete();
        });

        Schema::table('excuse_requests', function (Blueprint $table) {
            $table->foreign('attendance_record_id')->references('id')->on('attendance_records')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['cohort_id']);
            $table->dropForeign(['lab_group_id']);
        });

        Schema::table('attendance_ledger_entries', function (Blueprint $table) {
            $table->dropForeign(['attendance_record_id']);
        });

        Schema::table('excuse_requests', function (Blueprint $table) {
            $table->dropForeign(['attendance_record_id']);
        });
    }
};
