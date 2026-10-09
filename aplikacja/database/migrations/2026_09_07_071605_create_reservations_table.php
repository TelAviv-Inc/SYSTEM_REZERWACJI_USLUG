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
        Schema::create('reservations', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->uuid('user_id');
            $table->uuid('employee_id');
            $table->uuid('service_id');
            $table->date('reservation_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['pending','confirmed', 'completed', 'cancelled'])->default('pending');
            $table->string('comment')->nullable();
            $table->timestamps();

           $table->foreign('user_id')->references('uuid')->on('users');
           $table->foreign('employee_id')->references('uuid')->on('employees');
           $table->foreign('service_id')->references('uuid')->on('services');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
