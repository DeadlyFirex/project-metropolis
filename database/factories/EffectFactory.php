<?php

namespace Database\Factories;

use App\Models\Effect;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class EffectFactory extends Factory
{
    protected $model = Effect::class;

    /**
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement([
                'safety',
                'recreation',
                'climate',
                'facilities',
                'infrastructure',
            ]),
            # Can be between -10 and 10
            'value' => $this->faker->numberBetween(-10, 10),
            'module_id' => Module::factory(),
        ];
    }
}
