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
        Schema::table('events', function (Blueprint $table) {
            // weg met relatieve velden
            $table->dropColumn(['duration', 'recurring_interval']);

            // duration-flag vervangen door simpel bool
            $table->renameColumn('recurring', 'is_recurring');   // als je al 'recurring' had
            $table->boolean('is_recurring')->default(false)->change();

            // alleen de TIJD nodig; datumdeel gebruiken we dynamisch
            $table->time('start_time')->nullable()->change();
            $table->time('end_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
