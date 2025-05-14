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
                'name' => 'Politiebureau',
                'description' => 'Een politiebureau waar veiligheid en wetshandhaving centraal staan.',
                'category' => 'Veiligheid',
                'image' => 'politiebureau.jpg',
            ],
            [
                'name' => 'Brandweerkazerne',
                'description' => 'Een brandweerkazerne waar brandweerwagens en personeel zich voorbereiden op noodsituaties.',
                'category' => 'Veiligheid',
                'image' => 'brandweerkazerne.jpg',
            ],
            [
                'name' => 'Park',
                'description' => 'Een openbaar park voor ontspanning, natuurbeleving en beweging.',
                'category' => 'Recreatie',
                'image' => 'park.jpg',
            ],
            [
                'name' => 'Bioscoop',
                'description' => 'Een bioscoop waar films en andere voorstellingen worden vertoond voor publiek.',
                'category' => 'Recreatie',
                'image' => 'bioscoop.jpg',
            ],
            [
                'name' => 'Sportpark',
                'description' => 'Een sportpark voor diverse sportactiviteiten en wedstrijden.',
                'category' => 'Recreatie',
                'image' => 'sportpark.jpg',
            ],
            [
                'name' => 'Waterzuivering',
                'description' => 'Een waterzuiveringsinstallatie die zorgt voor het schoonmaken van afvalwater.',
                'category' => 'Milieukwaliteit',
                'image' => 'waterzuivering.jpg',
            ],
            [
                'name' => 'School',
                'description' => 'Een leeromgeving die ontwikkeling en sociale groei bevordert.',
                'category' => 'Voorzieningen',
                'image' => 'school.jpg',
            ],
            [
                'name' => 'Winkel',
                'description' => 'Een winkel waar consumenten producten kunnen kopen in verschillende categorieën.',
                'category' => 'Voorzieningen',
                'image' => 'winkel.jpg',
            ],
            [
                'name' => 'Ziekenhuis',
                'description' => 'Een instelling voor medische zorg, behandeling en operaties.',
                'category' => 'Voorzieningen',
                'image' => 'ziekenhuis.jpg',
            ],
            [
                'name' => 'Station',
                'description' => 'Een station voor treinen, waar mensen van en naar verschillende bestemmingen kunnen reizen.',
                'category' => 'Mobiliteit',
                'image' => 'station.jpg',
            ],
            [
                'name' => 'Weg',
                'description' => 'Een weg die automobilisten, fietsers en voetgangers in staat stelt zich tussen steden of gebieden te verplaatsen.',
                'category' => 'Mobiliteit',
                'image' => 'weg.jpg',
            ],
            [
                'name' => 'Fietspad',
                'description' => 'Een fietspad speciaal voor fietsers om veilig te kunnen rijden.',
                'category' => 'Mobiliteit',
                'image' => 'fietspad.jpg',
            ],
            [
                'name' => 'Tankstation',
                'description' => 'Een tankstation waar voertuigen brandstof kunnen tanken en andere autogerelateerde producten kunnen kopen.',
                'category' => 'Mobiliteit',
                'image' => 'tankstation.jpg',
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
