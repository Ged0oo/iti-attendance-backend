<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_ledger_id')->constrained('attendance_ledgers')->cascadeOnDelete();
            
            // TODO: uncomment when M4 merges
            // $table->foreignId('attendance_record_id')->nullable()->constrained('attendance_records')->nullOnDelete();
            $table->unsignedBigInteger('attendance_record_id')->nullable();
            
            $table->integer('delta')->default(-25);
            $table->integer('balance_after');
            $table->string('reason')->nullable();
            
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_ledger_entries');
    }
};
