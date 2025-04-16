<?php

namespace Database\Factories;

use App\Models\Configuration;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigurationFactory extends Factory
{
    protected $model = Configuration::class;

    /**
     * @throws \JsonException
     */
    public function definition(): array
    {
        return [
            'name' => 'Configuration ' . $this->faker->unique()->word,
            'user_id' => User::factory(),
            'modules' => json_encode(
                [
                    Module::factory(), Module::factory(), Module::factory()
                ], JSON_THROW_ON_ERROR),
            'factors' => json_encode(
                [
                    'factor1' => $this->faker->randomFloat(2, 0, 100),
                    'factor2' => $this->faker->randomFloat(2, 0, 100),
                    'factor3' => $this->faker->randomFloat(2, 0, 100),
                ], JSON_THROW_ON_ERROR),
        ];
    }
}
