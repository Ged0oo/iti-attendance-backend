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
        Schema::table('engagements', function (Blueprint $table) {
            // tops up the shared engagements table so an engagement can point at a course
            // (stays null for business sessions, which have no course)
            if (! Schema::hasColumn('engagements', 'course_id')) {
                $table->foreignId('course_id')->nullable()->after('cohort_id')->constrained()->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('engagements', function (Blueprint $table) {
            if (Schema::hasColumn('engagements', 'course_id')) {
                $table->dropConstrainedForeignId('course_id');
            }
        });
    }
};
