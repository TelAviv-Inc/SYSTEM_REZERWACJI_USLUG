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
        Schema::create('services', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->uuid("category_id")->nullable();
            $table->string('name', 128);
            $table->string('description', 400);
            $table->smallInteger('duration', false)->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->tinyInteger('active', false)->default(1);
            $table->timestamps();

            $table->foreign('category_id')->references('uuid')->on('service_categories')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
