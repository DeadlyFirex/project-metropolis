<?php

namespace Database\Factories;

use App\Models\Slot;
use App\Models\Configuration;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class SlotFactory extends Factory
{
    protected $model = Slot::class;

    public function definition(): array
    {
        return [
            'row' => $this->faker->numberBetween(0, 2),
            'column' => $this->faker->numberBetween(0, 3),
            'module_id' => Module::factory(),
        ];
    }
}
