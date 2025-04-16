<?php

namespace Database\Factories;

use App\Models\Slot;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class SlotFactory extends Factory
{
    protected $model = Slot::class;

    public function definition(): array
    {
        /*
         * The generation is based on the following assumptions:
         * - The timetable is a 3x4 grid (3 rows and 4 columns).
         * - Each slot is represented by a row and column index that is unique.
         * This may create a conflict if the same row and column are generated for different slots.
         * TODO: Implement a mechanism to ensure that each slot has a unique row and column combination.
         */
        return [
            'row' => $this->faker->numberBetween(0, 2),
            'column' => $this->faker->numberBetween(0, 3),
            'module_id' => Module::factory(),
        ];
    }
}
