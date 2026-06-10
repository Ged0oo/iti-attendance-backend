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
            $table->foreignId('lab_group_id')
                ->nullable()
                ->after('cohort_id')
                ->constrained()
                ->nullOnDelete();

            $table->index(['cohort_id', 'lab_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['cohort_id', 'lab_group_id']);
            $table->dropConstrainedForeignId('lab_group_id');
        });
    }
};
