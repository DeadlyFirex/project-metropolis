<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    protected $model = Module::class;

    /**
     * @throws \JsonException
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'category' => $this->faker->word,
            'image_path' => 'images/placeholder.jpg',

            'factors' => json_encode([
                'factor1' => $this->faker->randomFloat(2, 0, 100),
                'factor2' => $this->faker->randomFloat(2, 0, 100),
                'factor3' => $this->faker->randomFloat(2, 0, 100),
            ], JSON_THROW_ON_ERROR),
        ];
    }
}
