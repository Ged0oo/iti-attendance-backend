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
        Schema::create('billing_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // the instructor
            $table->string('compensation_type'); // external or internal
            $table->decimal('scheduled_hours', 8, 2)->default(0);
            $table->decimal('delivered_hours', 8, 2)->default(0);
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->decimal('fixed_salary', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0); // filled in by the billing service
            $table->date('billing_period_start');
            $table->date('billing_period_end');
            $table->string('status')->default('draft'); // draft, finalized or forwarded
            $table->timestamps();

            $table->index(['cohort_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_records');
    }
};
