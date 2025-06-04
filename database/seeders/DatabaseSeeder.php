<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ModuleSeeder::class,
            ConfigurationSeeder::class,
            SlotSeeder::class,
            EffectSeeder::class,
            EventTypeSeeder::class,
            EventEffectSeeder::class,
            CategorySeeder::class,
        ]);
    }
}
