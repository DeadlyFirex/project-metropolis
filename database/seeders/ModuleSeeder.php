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
                'name' => 'Ziekenhuis',
                'description' => 'Een instelling voor medische zorg, behandeling en operaties.',
                'category' => 'Care',
                'image' => 'ziekenhuis.jpg',
            ],
            [
                'name' => 'Huis',
                'description' => 'Een fijne plek om thuis te komen en tot rust te komen.',
                'category' => 'Residential',
                'image' => 'huis.jpg',
            ],
            [
                'name' => 'Park',
                'description' => 'Een openbaar park voor ontspanning, natuurbeleving en beweging.',
                'category' => 'Public Space',
                'image' => 'park.jpg',
            ],
            [
                'name' => 'Basisschool',
                'description' => 'Een leeromgeving die ontwikkeling en sociale groei bevordert.',
                'category' => 'Education',
                'image' => 'school.jpg',
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
            ]);
        }
    }
}
