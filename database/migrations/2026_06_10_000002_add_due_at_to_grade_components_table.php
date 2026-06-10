<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_components', function (Blueprint $table) {
            $table->timestamp('due_at')->nullable()->after('is_deliverable');
        });
    }

    public function down(): void
    {
        Schema::table('grade_components', function (Blueprint $table) {
            $table->dropColumn('due_at');
        });
    }
};
