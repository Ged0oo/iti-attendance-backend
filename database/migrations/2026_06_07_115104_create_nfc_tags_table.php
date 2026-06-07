<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nfc_tags', function (Blueprint $table) {
            $table->id();
            // Link to M5's students table
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            
            // The physical hardware serial number of the NFC chip
            $table->string('serial_number')->unique(); 
            
            // Security states
            $table->enum('status', ['active', 'lost', 'revoked'])->default('active');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nfc_tags');
    }
};