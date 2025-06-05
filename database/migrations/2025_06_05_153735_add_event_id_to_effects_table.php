// 2025_06_06_000000_add_event_id_to_effects_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('effects', function (Blueprint $table) {
            $table->foreignId('event_id')
                ->nullable()
                ->constrained('events')
                ->onDelete('cascade')
                ->after('module_id');
        });
    }

    public function down(): void
    {
        Schema::table('effects', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
        });
    }
};
