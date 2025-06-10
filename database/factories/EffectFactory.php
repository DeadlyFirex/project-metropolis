<?php

namespace Database\Factories;

use App\Models\Effect;
use App\Models\EventType; // Importeer EventType model
use Illuminate\Database\Eloquent\Factories\Factory;

class EffectFactory extends Factory
{
    protected $model = Effect::class;

    /**
     * Definieer de standaard staat van het model.
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
            # Kan tussen -10 en 10 liggen
            'value' => $this->faker->numberBetween(-10, 10),
            // Correctie: Verwijder 'module_id' en voeg 'event_type_id' toe.
            // De 'event_effects' tabel (gekoppeld aan het Effect model) heeft 'event_type_id', niet 'module_id'.
            'event_type_id' => EventType::factory(), // Koppelt aan een bestaand EventType
            'is_primary_effect' => $this->faker->boolean(),
            'is_adjacent_effect' => $this->faker->boolean(),
        ];
    }
}
