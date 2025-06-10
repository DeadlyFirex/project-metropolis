<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EventEffect;
use App\Models\EventType;
use Database\Factories\PrimaryEventEffectFactory;
use Database\Factories\AdjacentEventEffectFactory;

class EventEffectSeeder extends Seeder
{
    public function run()
    {
        $festivalType = EventType::where('name', 'Festival')->first();
        if (!$festivalType) {
            $festivalType = EventType::factory()->create(['name' => 'Festival']);
        }

        $concertType = EventType::where('name', 'Concert')->first();
        if (!$concertType) {
            $concertType = EventType::factory()->create(['name' => 'Concert']);
        }

        $marktType = EventType::where('name', 'Markt')->first();
        if (!$marktType) {
            $marktType = EventType::factory()->create(['name' => 'Markt']);
        }

        // Festival effecten (nu via factories!)
        PrimaryEventEffectFactory::new()->festivalSafety()->for($festivalType, 'eventType')->create();
        PrimaryEventEffectFactory::new()->festivalRecreation()->for($festivalType, 'eventType')->create();
        AdjacentEventEffectFactory::new()->festivalSafety()->for($festivalType, 'eventType')->create();

        // Concert effecten
        PrimaryEventEffectFactory::new()->state([
            'type' => 'safety',
            'value' => -2,
        ])->for($concertType, 'eventType')->create();
        PrimaryEventEffectFactory::new()->state([
            'type' => 'recreation',
            'value' => 10,
        ])->for($concertType, 'eventType')->create();
        PrimaryEventEffectFactory::new()->state([
            'type' => 'climate',
            'value' => -2,
        ])->for($concertType, 'eventType')->create();
        PrimaryEventEffectFactory::new()->state([
            'type' => 'facilities',
            'value' => 2,
        ])->for($concertType, 'eventType')->create();
        PrimaryEventEffectFactory::new()->state([
            'type' => 'infrastructure',
            'value' => -1,
        ])->for($concertType, 'eventType')->create();

        // Aangrenzende effecten voor Concert met faker tussen -10 en 10
        $adjacentEffectTypes = ['safety', 'recreation', 'climate', 'facilities', 'infrastructure'];
        foreach ($adjacentEffectTypes as $type) {
            AdjacentEventEffectFactory::new()->randomValue()->state(['type' => $type])->for($concertType, 'eventType')->create();
        }

        // Markt effecten
        PrimaryEventEffectFactory::new()->state([
            'type' => 'safety',
            'value' => -1,
        ])->for($marktType, 'eventType')->create();
        PrimaryEventEffectFactory::new()->state([
            'type' => 'recreation',
            'value' => 2,
        ])->for($marktType, 'eventType')->create();
        PrimaryEventEffectFactory::new()->state([
            'type' => 'climate',
            'value' => -2,
        ])->for($marktType, 'eventType')->create();
        PrimaryEventEffectFactory::new()->state([
            'type' => 'facilities',
            'value' => 3,
        ])->for($marktType, 'eventType')->create();
        PrimaryEventEffectFactory::new()->state([
            'type' => 'infrastructure',
            'value' => -5,
        ])->for($marktType, 'eventType')->create();

        // Aangrenzende effecten voor Markt met faker tussen -10 en 10
        foreach ($adjacentEffectTypes as $type) {
            AdjacentEventEffectFactory::new()->randomValue()->state(['type' => $type])->for($marktType, 'eventType')->create();
        }
    }
}
