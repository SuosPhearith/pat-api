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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('image', 500)->nullable();
            $table->foreignId('continent_id')->constrained()->onDelete('cascade');
            $table->string('population', 50);
            $table->string('territory', 50);
            $table->text('description');
            $table->timestamps();
            
            // Optional: Add index for better performance
            $table->index('continent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};