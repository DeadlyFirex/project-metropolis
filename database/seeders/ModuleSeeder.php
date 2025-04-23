<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    public function run()
    {
        $modules = [
            [
                'name' => 'Hospital',
                'description' => 'Een instelling voor medische zorg, behandeling en operaties.',
                'category' => 'Care',
                'image' => 'ziekenhuis.jpg',
                'factors' => [
                    'gezondheid' => 0.95,
                    'happiness' => 0.4,
                    'mentale_rust' => 0.3,
                    'sociale_interactie' => 0.6,
                ],
            ],
            [
                'name' => 'Home',
                'description' => 'Een fijne plek om thuis te komen en tot rust te komen.',
                'category' => 'Residential',
                'image' => 'huis.jpg',
                'factors' => [
                    'gezondheid' => 0.7,
                    'happiness' => 0.9,
                    'mentale_rust' => 0.85,
                    'sociale_interactie' => 0.6,
                ],
            ],
            [
                'name' => 'Park',
                'description' => 'Een openbaar park voor ontspanning, natuurbeleving en beweging.',
                'category' => 'Public Space',
                'image' => 'park.jpg',
                'factors' => [
                    'gezondheid' => 0.8,
                    'happiness' => 0.95,
                    'mentale_rust' => 0.9,
                    'sociale_interactie' => 0.7,
                ],
            ],
            [
                'name' => 'Preschool',
                'description' => 'Een leeromgeving die ontwikkeling en sociale groei bevordert.',
                'category' => 'Education',
                'image' => 'school.jpg',
                'factors' => [
                    'gezondheid' => 0.6,
                    'happiness' => 0.7,
                    'mentale_rust' => 0.5,
                    'sociale_interactie' => 0.95,
                ],
            ],
        ];

        foreach ($modules as $mod) {
            $imagePath = 'modules/' . $mod['image'];
            $sourceImage = database_path('seeders/images/' . $mod['image']);

            if (file_exists($sourceImage)) {
                Storage::disk('public')->putFileAs('modules', new \Illuminate\Http\File($sourceImage), $mod['image']);
            }

            Module::create([
                'name' => $mod['name'],
                'description' => $mod['description'],
                'category' => $mod['category'],
                'image_path' => $imagePath,
                'factors' => json_encode($mod['factors'], JSON_THROW_ON_ERROR),
            ]);
        }
    }
}
