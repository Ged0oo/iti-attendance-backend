<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Same idea as engagements: skip while the temporary table exists, take
        // over once that create is removed. Runs before attendance_records so its
        // session_id foreign key still resolves.
        if (Schema::hasTable('sessions')) {
            return;
        }

        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engagement_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('scheduled_hours', 4, 2);
            $table->boolean('is_delivered')->default(false);
            $table->string('qr_code')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // No drop here, see the engagements migration note.
    }
};
