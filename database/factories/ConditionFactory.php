<?php

namespace Database\Factories;

use App\Models\Condition;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConditionFactory extends Factory
{
    protected $model = Condition::class;

    public function definition()
    {
        return [
            'category' => $this->faker->word(),
            'max' => 1,
            'incompatible' => [],
        ];
    }
}
