<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Veiligheid',
                'description' => 'De veiligheid van de wijk, hoe veilig mensen zich voelen.',
            ],
            [
                'name' => 'Recreatie',
                'description' => 'De recreatieve voorzieningen in de wijk, zoals parken en speeltuinen.',
            ],
            [
                'name' => 'Milieukwaliteit',
                'description' => 'De kwaliteit van het milieu in de wijk, zoals lucht- en geluidskwaliteit.',
            ],
            [
                'name' => 'Voorzieningen',
                'description' => 'De beschikbaarheid van voorzieningen zoals winkels en scholen.',
            ],
            [
                'name' => 'Mobiliteit',
                'description' => 'De bereikbaarheid en verkeerssituatie in de wijk.',
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'type' => $category['name'],
                'value' => $category['description'],
            ]);
        }
    }
}
