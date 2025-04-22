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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')
                ->comment("A short description of the module");

            // TODO: We'd need an ENUM for this one;
            $table->string('category');

            $table->string('image_path')
                ->default('default-image.png');
            $table->json('factors')
                ->comment("Depends on if a list of factors is given");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
