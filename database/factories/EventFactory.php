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
            'image_path' => 'images/placeholder.jpg',
            'event_type_id' => EventType::factory(),
            'duration' => $this->faker->numberBetween(600, 3600),
        ];
    }
}
