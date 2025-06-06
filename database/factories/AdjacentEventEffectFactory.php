<?php

namespace Database\Factories;

use App\Models\EventEffect;
use App\Models\EventType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdjacentEventEffectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EventEffect::class;

    /**
     * Define the model's default state for an adjacent effect.
     *
     * @return array<string, mixed>
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
            'value' => $this->faker->numberBetween(1, 5), // Typische waardes voor aangrenzende effecten
            'event_type_id' => EventType::factory(), // Maakt een EventType aan als er geen expliciet gekoppeld is
            'is_primary_effect' => false, // Altijd false voor deze factory
            'is_adjacent_effect' => true, // Altijd true voor deze factory
        ];
    }

    /**
     * State for the Festival's specific adjacent safety effect (-1).
     */
    public function festivalSafety()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'safety',
                'value' => -1,
            ];
        });
    }

    /**
     * State for a random adjacent effect value between -10 and 10.
     */
    public function randomValue()
    {
        return $this->state(function (array $attributes) {
            return [
                'value' => $this->faker->numberBetween(-10, 10),
            ];
        });
    }
}
