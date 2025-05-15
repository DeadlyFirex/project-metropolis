<?php

namespace Database\Seeders;

use App\Models\Effect;
use Illuminate\Database\Seeder;

class EffectSeeder extends Seeder
{
    public function run()
    {
        $effects = [
            // Politiebureau
            [
                'type' => 'safety',
                'value' => 5,
                'module_id' => 1,
            ],
            [
                'type' => 'recreation',
                'value' => 1,
                'module_id' => 1,
            ],
            [
                'type' => 'climate',
                'value' => 0,
                'module_id' => 1,
            ],
            [
                'type' => 'facilities',
                'value' => 1,
                'module_id' => 1,
            ],
            [
                'type' => 'infrastructure',
                'value' => 2,
                'module_id' => 1,
            ],

            // Brandweerkazerne
            [
                'type' => 'safety',
                'value' => 4,
                'module_id' => 2,
            ],
            [
                'type' => 'recreation',
                'value' => 1,
                'module_id' => 2,
            ],
            [
                'type' => 'climate',
                'value' => 2,
                'module_id' => 2,
            ],
            [
                'type' => 'facilities',
                'value' => 1,
                'module_id' => 2,
            ],
            [
                'type' => 'infrastructure',
                'value' => 2,
                'module_id' => 2,
            ],

            // Park
            [
                'type' => 'safety',
                'value' => -2,
                'module_id' => 3,
            ],
            [
                'type' => 'recreation',
                'value' => 5,
                'module_id' => 3,
            ],
            [
                'type' => 'climate',
                'value' => 4,
                'module_id' => 3,
            ],
            [
                'type' => 'facilities',
                'value' => 0,
                'module_id' => 3,
            ],
            [
                'type' => 'infrastructure',
                'value' => 0,
                'module_id' => 3,
            ],

            // Bioscoop
            [
                'type' => 'safety',
                'value' => -1,
                'module_id' => 4,
            ],
            [
                'type' => 'recreation',
                'value' => 4,
                'module_id' => 4,
            ],
            [
                'type' => 'climate',
                'value' => 0,
                'module_id' => 4,
            ],
            [
                'type' => 'facilities',
                'value' => 2,
                'module_id' => 4,
            ],
            [
                'type' => 'infrastructure',
                'value' => 0,
                'module_id' => 4,
            ],

            // Sportpark
            [
                'type' => 'safety',
                'value' => 0,
                'module_id' => 5,
            ],
            [
                'type' => 'recreation',
                'value' => 5,
                'module_id' => 5,
            ],
            [
                'type' => 'climate',
                'value' => 2,
                'module_id' => 5,
            ],
            [
                'type' => 'facilities',
                'value' => 3,
                'module_id' => 5,
            ],
            [
                'type' => 'infrastructure',
                'value' => 0,
                'module_id' => 5,
            ],

            // Waterzuivering
            [
                'type' => 'safety',
                'value' => 0,
                'module_id' => 6,
            ],
            [
                'type' => 'recreation',
                'value' => 0,
                'module_id' => 6,
            ],
            [
                'type' => 'climate',
                'value' => 5,
                'module_id' => 6,
            ],
            [
                'type' => 'facilities',
                'value' => 2,
                'module_id' => 6,
            ],
            [
                'type' => 'infrastructure',
                'value' => 0,
                'module_id' => 6,
            ],

            // School
            [
                'type' => 'safety',
                'value' => 2,
                'module_id' => 7,
            ],
            [
                'type' => 'recreation',
                'value' => 2,
                'module_id' => 7,
            ],
            [
                'type' => 'climate',
                'value' => 0,
                'module_id' => 7,
            ],
            [
                'type' => 'facilities',
                'value' => 5,
                'module_id' => 7,
            ],
            [
                'type' => 'infrastructure',
                'value' => -3,
                'module_id' => 7,
            ],

            // Winkel
            [
                'type' => 'safety',
                'value' => 0,
                'module_id' => 8,
            ],
            [
                'type' => 'recreation',
                'value' => 0,
                'module_id' => 8,
            ],
            [
                'type' => 'climate',
                'value' => -2,
                'module_id' => 8,
            ],
            [
                'type' => 'facilities',
                'value' => 5,
                'module_id' => 8,
            ],
            [
                'type' => 'infrastructure',
                'value' => 0,
                'module_id' => 8,
            ],

            // Ziekenhuis
            [
                'type' => 'safety',
                'value' => 3,
                'module_id' => 9,
            ],
            [
                'type' => 'recreation',
                'value' => 0,
                'module_id' => 9,
            ],
            [
                'type' => 'climate',
                'value' => 0,
                'module_id' => 9,
            ],
            [
                'type' => 'facilities',
                'value' => 5,
                'module_id' => 9,
            ],
            [
                'type' => 'infrastructure',
                'value' => 0,
                'module_id' => 9,
            ],

            // Station
            [
                'type' => 'safety',
                'value' => -2,
                'module_id' => 10,
            ],
            [
                'type' => 'recreation',
                'value' => 2,
                'module_id' => 10,
            ],
            [
                'type' => 'climate',
                'value' => 0,
                'module_id' => 10,
            ],
            [
                'type' => 'facilities',
                'value' => 4,
                'module_id' => 10,
            ],
            [
                'type' => 'infrastructure',
                'value' => 5,
                'module_id' => 10,
            ],

            // Weg
            [
                'type' => 'safety',
                'value' => -4,
                'module_id' => 11,
            ],
            [
                'type' => 'recreation',
                'value' => 2,
                'module_id' => 11,
            ],
            [
                'type' => 'climate',
                'value' => -4,
                'module_id' => 11,
            ],
            [
                'type' => 'facilities',
                'value' => 3,
                'module_id' => 11,
            ],
            [
                'type' => 'infrastructure',
                'value' => 5,
                'module_id' => 11,
            ],

            // Fietspad
            [
                'type' => 'safety',
                'value' => 0,
                'module_id' => 12,
            ],
            [
                'type' => 'recreation',
                'value' => 3,
                'module_id' => 12,
            ],
            [
                'type' => 'climate',
                'value' => 3,
                'module_id' => 12,
            ],
            [
                'type' => 'facilities',
                'value' => 3,
                'module_id' => 12,
            ],
            [
                'type' => 'infrastructure',
                'value' => 4,
                'module_id' => 12,
            ],

            // Tankstation
            [
                'type' => 'safety',
                'value' => -2,
                'module_id' => 13,
            ],
            [
                'type' => 'recreation',
                'value' => 0,
                'module_id' => 13,
            ],
            [
                'type' => 'climate',
                'value' => -4,
                'module_id' => 13,
            ],
            [
                'type' => 'facilities',
                'value' => 1,
                'module_id' => 13,
            ],
            [
                'type' => 'infrastructure',
                'value' => 4,
                'module_id' => 13,
            ],
        ];

        foreach ($effects as $mod) {
            Effect::create([
                'type' => $mod['type'],
                'value' => $mod['value'],
                'module_id' => $mod['module_id']
            ]);
        }
    }
}
