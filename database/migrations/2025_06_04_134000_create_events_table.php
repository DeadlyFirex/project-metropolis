<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->foreignId('event_type_id')
                ->constrained('event_types')
                ->onDelete('cascade');
            $table->unsignedInteger('duration')
                ->default(600);
            $table->boolean('recurring')->default(false);
            $table->unsignedInteger('recurring_interval')->nullable();
            $table->DateTime('start_time')
                ->default(now());
            $table->DateTime('end_time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
