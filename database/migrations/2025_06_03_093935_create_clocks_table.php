<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /**
         * This migration creates a 'clocks' table to store user's clock state
         * used for the state of modules and events.
         * This table is user specific.
         */
        Schema::create('clocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');
            $table->time('time')->default('00:00:00');
            $table->date('date')->default(now()->toDateString());
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clocks');
    }
};
