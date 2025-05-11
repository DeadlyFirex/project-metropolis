<?php

namespace Database\Seeders;

use App\Models\Effect;
use Illuminate\Database\Seeder;

class EffectSeeder extends Seeder
{
    public function run()
    {
        $effects = [
            // Hospital
            [
                'type' => 'safety',
                'value' => 3,
                'module_id' => 1,
            ],
            [
                'type' => 'recreation',
                'value' => 0,
                'module_id' => 1,
            ],
            [
                'type' => 'climate',
                'value' => 0,
                'module_id' => 1,
            ],
            [
                'type' => 'facilities',
                'value' => 5,
                'module_id' => 1,
            ],
            [
                'type' => 'infrastructure',
                'value' => 0,
                'module_id' => 1,
            ],

            // Home
            [
                'type' => 'safety',
                'value' => 3,
                'module_id' => 2,
            ],
            [
                'type' => 'recreation',
                'value' => 1,
                'module_id' => 2,
            ],
            [
                'type' => 'climate',
                'value' => -1,
                'module_id' => 2,
            ],
            [
                'type' => 'facilities',
                'value' => 2,
                'module_id' => 2,
            ],
            [
                'type' => 'infrastructure',
                'value' => 0,
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

            // School
            [
                'type' => 'safety',
                'value' => 2,
                'module_id' => 4,
            ],
            [
                'type' => 'recreation',
                'value' => 2,
                'module_id' => 4,
            ],
            [
                'type' => 'climate',
                'value' => 0,
                'module_id' => 4,
            ],
            [
                'type' => 'facilities',
                'value' => 5,
                'module_id' => 4,
            ],
            [
                'type' => 'infrastructure',
                'value' => -3,
                'module_id' => 4,
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
