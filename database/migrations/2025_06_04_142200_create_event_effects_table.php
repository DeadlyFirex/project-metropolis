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
        Schema::create('event_effects', function (Blueprint $table) {
            $table->id();
            // Gebruik van enum voor het type, zoals door jou aangeleverd
            $table->enum('type',
                [
                    'safety',
                    'recreation',
                    'climate',
                    'facilities',
                    'infrastructure',
                ]
            )->comment('Type of effect');
            $table->integer('value');
            // Module ID foreign key
            $table->foreignId('module_id')
                ->nullable()
                ->constrained('modules')
                ->onDelete('cascade');
            // Gedetailleerde foreignId, zoals door jou aangeleverd
            $table->foreignId('event_type_id')
                ->nullable()
                ->constrained('event_types') // Specificeer de tabelnaam
                ->onDelete('cascade');
            // Cruciale boolean kolommen die eerder ontbraken
            $table->boolean('is_primary_effect')->default(false);
            $table->boolean('is_adjacent_effect')->default(false);
            $table->boolean('affects_adjacent')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_effects');
    }
};
