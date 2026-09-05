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
        Schema::create('employee_services', function (Blueprint $table) {
            $table->uuid('employee_id');
            $table->uuid('service_id');
            $table->timestamps();

            $table->foreign('employee_id')->references('uuid')->on('employees')->onDelete('cascade');
            $table->foreign('service_id')->references('uuid')
            ->on('services')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_services');
    }
};
