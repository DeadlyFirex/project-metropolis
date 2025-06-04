<?php

namespace Database\Factories;

use App\Models\EventEffect;
use App\Models\EventType;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventEffectFactory extends Factory
{
    protected $model = EventEffect::class;

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
            'event_type_id' => EventType::factory(),
        ];
    }
}
