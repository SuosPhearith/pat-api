<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
//     /**
//      * Run the migrations.
//      */
//     public function up(): void
//     {
//         Schema::create('cities', function (Blueprint $table) {
//             $table->id();
//             $table->string('name', 150);
//             $table->string('image', 200);
//             $table->integer('trip_days');
//             $table->string('price', 10);
//             $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
//             $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
//         });
//     }

//     /**
//      * Reverse the migrations.
//      */
//     public function down(): void
//     {
//         Schema::dropIfExists('cities');
//     }
// };
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('image', 500)->nullable();
            // $table->integer('trip_days');
            // $table->string('price', 10);
            $table->foreignId('country_id')
                  ->constrained('countries')
                  ->onDelete('cascade'); // Ensures deletion cascades

                  $table->timestamps(); // This adds both created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
