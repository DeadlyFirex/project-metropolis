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
        for ($row = 0; $row < 3; $row++) {
            for ($col = 0; $col < 4; $col++) {
                Slot::create([
                    'row' => $row,
                    'column' => $col,
                    'module_id' => Module::factory()->create()->id,
                ]);
            }
        }
    }
}
