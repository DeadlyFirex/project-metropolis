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

            // TODO: We need to split this into a separate table for categories
            $table->enum('category', [
                'Veiligheid',
                'Recreatie',
                'Milieukwaliteit',
                'Voorzieningen',
                'Mobiliteit',
            ]);

            $table->string('image_path')
                ->default('default-image.png');
            $table->timestamps();
            $table->softDeletes();
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
