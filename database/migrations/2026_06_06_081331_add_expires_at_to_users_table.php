<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add account expiry to the users table.
     *
     * NULL means the account never expires (Branch Manager).
     * A past timestamp means the account is locked.
     * A future timestamp means the account is currently active.
     *
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('password');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
