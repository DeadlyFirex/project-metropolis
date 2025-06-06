<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    protected $model = Module::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            // Ensure the category is one of the allowed ENUM values from the migration.
            // These values are defined in 2025_04_15_102537_create_modules_table.php
            'category' => $this->faker->randomElement([
                'Veiligheid',
                'Recreatie',
                'Milieukwaliteit',
                'Voorzieningen',
                'Mobiliteit',
            ]),
            'image_path' => 'images/placeholder.jpg',
        ];
    }
}
