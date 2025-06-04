<?php

namespace Database\Seeders;

use App\Models\EventEffect;
use Illuminate\Database\Seeder;

class EventEffectSeeder extends Seeder
{
    public function run()
    {
        $effects = [
            // Festival
            [
                'type' => 'safety',
                'value' => -3,
                'event_type_id' => 1,
            ],
            [
                'type' => 'recreation',
                'value' => 3,
                'event_type_id' => 1,
            ],
            [
                'type' => 'climate',
                'value' => -1,
                'event_type_id' => 1,
            ],
            [
                'type' => 'facilities',
                'value' => 1,
                'event_type_id' => 1,
            ],
            [
                'type' => 'infrastructure',
                'value' => -1,
                'event_type_id' => 1,
            ],

            // Concert
            [
                'type' => 'safety',
                'value' => -2,
                'event_type_id' => 2,
            ],
            [
                'type' => 'recreation',
                'value' => 4,
                'event_type_id' => 2,
            ],
            [
                'type' => 'climate',
                'value' => -2,
                'event_type_id' => 2,
            ],
            [
                'type' => 'facilities',
                'value' => 2,
                'event_type_id' => 2,
            ],
            [
                'type' => 'infrastructure',
                'value' => -1,
                'event_type_id' => 2,
            ],

            // Markt
            [
                'type' => 'safety',
                'value' => -1,
                'event_type_id' => 3,
            ],
            [
                'type' => 'recreation',
                'value' => 2,
                'event_type_id' => 3,
            ],
            [
                'type' => 'climate',
                'value' => -2,
                'event_type_id' => 3,
            ],
            [
                'type' => 'facilities',
                'value' => 3,
                'event_type_id' => 3,
            ],
            [
                'type' => 'infrastructure',
                'value' => -5,
                'event_type_id' => 3,
            ],
        ];

        foreach ($effects as $mod) {
            EventEffect::create([
                'type' => $mod['type'],
                'value' => $mod['value'],
                'event_type_id' => $mod['event_type_id']
            ]);
        }
    }
}
