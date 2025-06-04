<?php

namespace Database\Factories;

use App\Models\EventType;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventTypeFactory extends Factory
{
    protected $model = EventType::class;

    /**
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'image_path' => 'images/placeholder.jpg',
            'module_id' => Module::factory(),
            'min_duration' => $this->faker->numberBetween(300, 1800), // 5 to 30 minutes
            'max_duration' => $this->faker->numberBetween(1800, 3600), // 30 to 60 minutes
        ];
    }
}
