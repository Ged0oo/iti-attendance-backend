<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sessions ALTER COLUMN scheduled_hours TYPE numeric(4,2) USING scheduled_hours::numeric');
        } else {
            Schema::table('sessions', function (Blueprint $table) {
                $table->decimal('scheduled_hours', 4, 2)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sessions ALTER COLUMN scheduled_hours TYPE integer USING scheduled_hours::integer');
        } else {
            Schema::table('sessions', function (Blueprint $table) {
                $table->integer('scheduled_hours')->change();
            });
        }
    }
};
