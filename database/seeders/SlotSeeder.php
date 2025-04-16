<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Slot;
use Illuminate\Database\Seeder;

class SlotSeeder extends Seeder
{
    public function run(): void
    {
        // Create 12 slots with unique row and column combinations
        // TODO: Improve this to use a more efficient method
        for ($index = 0; $index < 12; $index++) {
            Slot::create([
                'index' => $index,
                'module_id' => Module::factory()->create()->id,
            ]);
        }
    }
}
