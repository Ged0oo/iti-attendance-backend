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
        // the engagements and sessions tables came in without timestamps,
        // top them up so Eloquent can track created_at / updated_at
        foreach (['engagements', 'sessions'] as $name) {
            if (! Schema::hasColumn($name, 'created_at')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->timestamps();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['engagements', 'sessions'] as $name) {
            if (Schema::hasColumn($name, 'created_at')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->dropTimestamps();
                });
            }
        }
    }
};
