<?php
namespace Tests\Feature;

use App\Models\Module;
use App\Models\User; // Voeg de User model import toe
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleHandlerControllerTest extends TestCase
{
    use RefreshDatabase; // Zorgt ervoor dat de database wordt hersteld voor elke test

    /** @test */
    public function it_can_store_a_module()
    {
        // Maak een testgebruiker aan
        $user = User::factory()->create();

        // Log de gebruiker in
        $this->actingAs($user);

        // Gebruik de factory om een module aan te maken
        // De factory genereert al een geldige categorie dankzij de eerdere update
        $module = \App\Models\Module::factory()->make();

        // Simuleer een POST-verzoek om de module op te slaan
        $response = $this->post(route('modules.store'), [
            'name' => $module->name,
            'description' => $module->description,
            'category' => $module->category, // Gebruik de geldige categorie van de factory
            'image' => null, // Geen afbeelding toevoegen voor de test
        ]);

        // Controleer of de module succesvol is opgeslagen
        $response->assertRedirect(route('module.index'));
        $this->assertDatabaseHas('modules', [
            'name' => $module->name,
            'description' => $module->description,
            'category' => $module->category,
            'image_path' => 'default-image.png', // Of de waarde die je voor test gebruikt
        ]);
    }

    /** @test */
    public function it_can_update_a_module()
    {
        // Maak een testgebruiker aan
        $user = User::factory()->create();

        // Log de gebruiker in
        $this->actingAs($user);

        // Maak een bestaande module aan met een geldige categorie
        $module = \App\Models\Module::factory()->create([
            'category' => 'Recreatie', // Zorg dat de initiële module een geldige categorie heeft
            'image_path' => 'default-image.png', // beginsituatie
        ]);

        // Geüpdatete gegevens (zonder afbeelding)
        $newData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            // Gebruik een geldige ENUM-waarde voor de update
            'category' => 'Veiligheid', // Aangepast naar een geldige ENUM-waarde
        ];

        // Simuleer een PUT-verzoek
        $response = $this->put(route('modules.update', $module->id), $newData);

        // Controleer redirect
        $response->assertRedirect(route('module.index'));

        // Controleer of de module is bijgewerkt (losse checks voor duidelijkheid)
        $this->assertDatabaseHas('modules', [
            'id' => $module->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'category' => 'Veiligheid', // Controleer met de nieuwe, geldige categorie
            'image_path' => 'default-image.png', // ← dit blijft staan als je niets uploadt
        ]);
    }

    /** @test */
    public function it_can_destroy_a_module()
    {
        // Maak een testgebruiker aan
        $user = User::factory()->create();

        // Log de gebruiker in
        $this->actingAs($user);

        // Maak een module aan met de factory (die nu een geldige categorie genereert)
        $module = \App\Models\Module::factory()->create();

        // Simuleer een DELETE-verzoek om de module te verwijderen
        $response = $this->delete(route('modules.destroy', $module->id));

        // Controleer of de module is verwijderd
        $response->assertRedirect(route('module.index'));
        $this->assertDatabaseMissing('modules', ['id' => $module->id]);
    }
}
