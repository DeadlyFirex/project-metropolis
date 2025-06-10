<?php

namespace Database\Factories;

use App\Models\EventEffect;
use App\Models\EventType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrimaryEventEffectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EventEffect::class;

    /**
     * Define the model's default state for a primary effect.
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
            'value' => $this->faker->numberBetween(-5, 5), // Typische waardes voor primaire effecten
            'event_type_id' => EventType::factory(), // Maakt een EventType aan als er geen expliciet gekoppeld is
            'is_primary_effect' => true, // Altijd true voor deze factory
            'is_adjacent_effect' => false, // Altijd false voor deze factory
        ];
    }

    /**
     * State for the Festival's specific primary safety effect (-2).
     */
    public function festivalSafety()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'safety',
                'value' => -2,
            ];
        });
    }

    /**
     * State for the Festival's specific primary recreation effect (+10).
     */
    public function festivalRecreation()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'recreation',
                'value' => 10,
            ];
        });
    }

}
