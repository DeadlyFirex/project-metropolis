<?php

namespace Database\Seeders;

use App\Models\EventType;
use App\Models\Module;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class EventTypeSeeder extends Seeder
{
    public function run()
    {
        $event_types = [
            [
                'name' => 'Festival',
                'description' => 'Een festival dat gehouden kan worden in een park.',
                'image' => 'festival.png',
                'module_id' => 3,
                'min_duration' => 3600,
                'max_duration' => 7200,
            ],
            [
                'name' => 'Concert',
                'description' => 'Een concert dat gehouden kan worden in een park.',
                'image' => 'concert.png',
                'module_id' => 3,
                'min_duration' => 2400,
                'max_duration' => 3600,
            ],
            [
                'name' => 'Markt',
                'description' => 'Een markt waar lokale producten en goederen worden verkocht.',
                'image' => 'market.png',
                'module_id' => 11,
                'min_duration' => 3600,
                'max_duration' => 7200,
            ],
        ];

        foreach ($event_types as $event_type) {
            $imagePath = 'events/' . $event_type['image'];
            $sourceImage = database_path('seeders/images/' . $event_type['image']);

            if (file_exists($sourceImage)) {
                Storage::disk('public')->putFileAs('events', new \Illuminate\Http\File($sourceImage), $event_type['image']);
            }

            EventType::create([
                'name' => $event_type['name'],
                'description' => $event_type['description'],
                'image_path' => $imagePath,
                'module_id' => $event_type['module_id'],
                'min_duration' => $event_type['min_duration'],
                'max_duration' => $event_type['max_duration'],
            ]);
        }
    }
}
