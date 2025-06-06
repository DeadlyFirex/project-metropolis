<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Event;
use App\Models\Slot;
use App\Models\EventType;
use App\Models\Effect;
use App\Models\Module; // Module model importeren
use Carbon\Carbon;

class EventControllerFeatureTest extends TestCase
{
    use RefreshDatabase; // Reset de database voor elke test

    protected function setUp(): void
    {
        // BELANGRIJK: Zorgt ervoor dat de Laravel applicatie wordt opgestart en de database klaar is.
        parent::setUp();

        // Voorbereiden van benodigde data voor tests
        // 'unguard()' wordt tijdelijk gebruikt om mass assignment protectie uit te schakelen
        // dit is een gangbare praktijk in tests om modelcreatie via factories soepeler te laten verlopen.
        Module::unguard();
        Slot::unguard();
        EventType::unguard();
        Event::unguard();
        Effect::unguard();

        // Maak een module aan met een geldige categorie uit de ENUM lijst in de migratie.
        // Categorie 'Veiligheid' is een geldige optie.
        $this->module = Module::factory()->create(['category' => 'Veiligheid']);

        // Explicit check of de module succesvol is aangemaakt.
        // Indien dit faalt, duidt het op een probleem met Module::factory() of de onderliggende setup.
        if (!$this->module instanceof Module) {
            throw new \RuntimeException('Module factory kon geen geldige Module instantie aanmaken. Controleer je ModuleFactory.php en database setup.');
        }

        // Maak een slot, event type en event aan die aan elkaar gekoppeld zijn.
        $this->slot = Slot::factory()->create(['module_id' => $this->module->id]);
        $this->eventType = EventType::factory()->create();
        $this->event = Event::factory()->create([
            'slot_id' => $this->slot->id,
            'event_type_id' => $this->eventType->id,
            'end_time' => Carbon::now()->addHours(1),
            'is_recurring' => false,
        ]);
        $this->slot->event_id = $this->event->id;
        $this->slot->save();

        // Maak effecten aan voor het event type.
        Effect::factory()->create([
            'event_type_id' => $this->eventType->id,
            'type' => 'safety',
            'value' => 10,
            'is_primary_effect' => true,
            'is_adjacent_effect' => false,
        ]);
        Effect::factory()->create([
            'event_type_id' => $this->eventType->id,
            'type' => 'recreation',
            'value' => 5,
            'is_primary_effect' => false,
            'is_adjacent_effect' => true,
        ]);

        // 'reguard()' wordt gebruikt om mass assignment protectie weer in te schakelen
        // na de setup fase.
        Module::reguard();
        Slot::reguard();
        EventType::reguard();
        Event::reguard();
        Effect::reguard();
    }

    /** @test */
    public function it_displays_the_event_dashboard()
    {
        // Simuleer een GET-verzoek naar het event dashboard.
        $response = $this->get('/event-dashboard'); // Ga er vanuit dat dit de route is voor de index methode.

        // Controleer of de statuscode 200 is (OK).
        $response->assertStatus(200);
        // Controleer of de correcte view wordt gebruikt.
        $response->assertViewIs('event_dashboard');
        // Controleer of alle verwachte variabelen aan de view zijn meegegeven.
        $response->assertViewHasAll(['event_types', 'slots', 'activeEvents', 'event_type_modules']);
    }

    /** @test */
    public function it_can_set_a_new_event_for_a_slot()
    {
        // Maak een nieuw, leeg slot aan voor de test.
        $newSlot = Slot::factory()->create(['module_id' => $this->module->id]);

        // Data voor het nieuwe event dat ingesteld moet worden.
        $eventData = [
            'event_name' => 'Test Event',
            'event_description' => 'Een beschrijving voor test event',
            'event_type' => $this->eventType->name, // Gebruik een bestaand event type
            'slot_id' => $newSlot->id,
            'duration' => 60, // 60 seconden
            'duration_unit' => 'minutes',
            'is_recurring' => false,
            'recurring_interval' => null,
            'recurring_unit' => null,
        ];

        // Simuleer een POST-verzoek om het event in te stellen.
        $response = $this->post('/events', $eventData); // Ga er vanuit dat /events de route is voor setEvent.

        // Controleer of de redirect succesvol is.
        $response->assertRedirect();
        // Controleer of er een succesbericht in de sessie staat.
        $response->assertSessionHas('success', 'Event succesvol ingesteld voor slot ' . $newSlot->id . '!');

        // Controleer of het event daadwerkelijk in de database is opgeslagen.
        $this->assertDatabaseHas('events', [
            'name' => 'Test Event',
            'slot_id' => $newSlot->id,
            'event_type_id' => $this->eventType->id,
        ]);

        // Controleer of het slot is bijgewerkt met de event_id.
        $this->assertDatabaseHas('slots', [
            'id' => $newSlot->id,
            'event_id' => Event::where('slot_id', $newSlot->id)->first()->id,
        ]);
    }

    /** @test */
    public function it_returns_error_if_event_type_not_found_when_setting_event()
    {
        // Maak een nieuw, leeg slot aan.
        $newSlot = Slot::factory()->create(['module_id' => $this->module->id]);

        // Data voor het event met een niet-bestaand event type.
        $eventData = [
            'event_name' => 'Test Event',
            'event_description' => 'Een beschrijving voor test event',
            'event_type' => 'NietBestaandEventType', // Ongeldig event type
            'slot_id' => $newSlot->id,
            'duration' => 60,
            'duration_unit' => 'minutes',
            'is_recurring' => false,
            'recurring_interval' => null,
            'recurring_unit' => null,
        ];

        // Simuleer het POST-verzoek.
        $response = $this->post('/events', $eventData);

        // Controleer of de redirect succesvol is.
        $response->assertRedirect();
        // Controleer of er een foutbericht in de sessie staat.
        $response->assertSessionHas('error', 'Geselecteerd event type niet gevonden!');
        // Controleer of het event niet in de database is opgeslagen.
        $this->assertDatabaseMissing('events', ['name' => 'Test Event']);
    }

    /** @test */
    public function it_can_reset_an_event_for_a_slot()
    {
        // Simuleer een POST-verzoek om het event van een slot te resetten.
        $response = $this->post('/events/reset', ['slot_id' => $this->slot->id]); // Ga er vanuit dat /events/reset de route is voor resetEvent.

        // Controleer of de redirect succesvol is.
        $response->assertRedirect();
        // Controleer of er een succesbericht in de sessie staat.
        $response->assertSessionHas('success', 'Event voor slot ' . $this->slot->id . ' is gereset naar normaal!');

        // Controleer of het event uit de database is verwijderd.
        $this->assertDatabaseMissing('events', ['id' => $this->event->id]);
        // Controleer of de event_id van het slot op null is gezet.
        $this->assertDatabaseHas('slots', [
            'id' => $this->slot->id,
            'event_id' => null,
        ]);
    }

    /** @test */
    public function it_returns_error_if_slot_not_found_when_resetting_event()
    {
        // Simuleer een POST-verzoek met een niet-bestaand slot ID.
        $response = $this->post('/events/reset', ['slot_id' => 9999]); // Niet-bestaand slot ID

        // Controleer of de redirect succesvol is.
        $response->assertRedirect();
        // Controleer of er een foutbericht in de sessie staat.
        $response->assertSessionHas('error', 'Slot niet gevonden!');
    }

    /** @test */
    public function it_returns_error_if_no_event_set_for_slot_when_resetting()
    {
        // Maak een slot aan zonder een gekoppeld event.
        $emptySlot = Slot::factory()->create(['module_id' => $this->module->id, 'event_id' => null]);

        // Simuleer een POST-verzoek om het event van dit lege slot te resetten.
        $response = $this->post('/events/reset', ['slot_id' => $emptySlot->id]);

        // Controleer of de redirect succesvol is.
        $response->assertRedirect();
        // Controleer of er een foutbericht in de sessie staat.
        $response->assertSessionHas('error', 'Geen event ingesteld voor slot ' . $emptySlot->id . '!');
    }

    /** @test */
    public function it_gets_all_active_slot_events_via_api()
    {
        // Simuleer een GET-verzoek naar de API om actieve slot events op te halen.
        $response = $this->get('/api/slot-events'); // Ga er vanuit dat dit de route is voor getSlotEvents.

        // Controleer of de statuscode 200 is (OK).
        $response->assertStatus(200);
        // Controleer de JSON-structuur van de respons.
        $response->assertJsonStructure([
            $this->slot->id => [
                'slot_id',
                'event_id',
                'event_name',
                'description',
                'start_time',
                'end_time',
                'is_recurring',
                'time_remaining',
                'effects',
                'is_primary'
            ]
        ]);

        // Controleer specifieke waarden in de respons.
        $responseData = $response->json();
        $this->assertArrayHasKey($this->slot->id, $responseData);
        $this->assertEquals($this->event->name, $responseData[$this->slot->id]['event_name']);
        $this->assertArrayHasKey('safety', $responseData[$this->slot->id]['effects']);
    }

    /** @test */
    public function it_gets_event_effects_via_api()
    {
        // Simuleer een GET-verzoek naar de API om event effecten op te halen.
        $response = $this->get('/api/event-effects/' . $this->event->id); // Ga er vanuit dat dit de route is voor getEventEffectsApi.

        // Controleer of de statuscode 200 is (OK).
        $response->assertStatus(200);
        // Controleer de JSON-structuur van de respons, waarbij alleen 'safety' verwacht wordt als primair effect.
        $response->assertJsonStructure(['effects' => ['safety']]);

        // Controleer de waarde van het 'safety' effect en bevestig dat 'recreation' (zijnde een aangrenzend effect) niet aanwezig is.
        $responseData = $response->json();
        $this->assertEquals(10, $responseData['effects']['safety']);
        $this->assertArrayNotHasKey('recreation', $responseData['effects']); // Aangrenzend effect zou hier niet moeten zijn
    }
}
