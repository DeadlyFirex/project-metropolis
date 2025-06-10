<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_clocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->time('clock_time')->default('00:00:00');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_clocks');
    }
};
