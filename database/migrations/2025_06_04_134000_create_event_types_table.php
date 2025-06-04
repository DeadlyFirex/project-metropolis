<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->string('image_path')
                ->default('default-image.png')
                ->comment("Path to the image representing the event type");
            // This indicates which module this event type is compatible with
            $table->foreignId('module_id')
                ->constrained('modules')
                ->onDelete('cascade');
            $table->unsignedInteger('min_duration')
                ->default(300);
            $table->unsignedInteger('max_duration')
                ->default(3600);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_types');
    }
};
