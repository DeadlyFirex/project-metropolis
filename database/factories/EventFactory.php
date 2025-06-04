<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'event_type_id' => EventType::factory(),
            'start_time' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'end_time' => function (array $attributes) {
                return (clone $attributes['start_time'])->addMinutes($this->faker->numberBetween(30, 120));
            },
        ];
    }
}
