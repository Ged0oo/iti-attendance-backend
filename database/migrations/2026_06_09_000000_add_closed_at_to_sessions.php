<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // "closed" (attendance finalised) is a different idea from "delivered"
        // (counts toward billing). Track closure on its own column.
        if (! Schema::hasColumn('sessions', 'closed_at')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->timestamp('closed_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sessions', 'closed_at')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropColumn('closed_at');
            });
        }
    }
};
