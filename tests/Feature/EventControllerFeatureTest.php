<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Event;
use App\Models\Slot;
use App\Models\EventType;
use App\Models\Effect;
use App\Models\Module;
use App\Models\User; // Importeer het User model
use Carbon\Carbon;

// Gebruik RefreshDatabase voor elke test om een schone database te garanderen.
// Dit vervangt de trait-gebruik in een traditionele PHPUnit class.
uses(RefreshDatabase::class);

// Voer deze code uit vóór elke test. Dit vervangt de setUp() methode.
beforeEach(function () {
    // Voorbereiden van benodigde data voor tests
    // 'unguard()' wordt tijdelijk gebruikt om mass assignment protectie uit te schakelen
    // dit is een gangbare praktijk in tests om modelcreatie via factories soepeler te laten verlopen.
    Module::unguard();
    Slot::unguard();
    EventType::unguard();
    Event::unguard();
    Effect::unguard();

    // Maak een dummy-gebruiker aan en log deze in.
    // Dit zorgt ervoor dat alle volgende verzoeken in de test worden uitgevoerd als een geauthenticeerde gebruiker.
    $user = User::factory()->create();
    $this->actingAs($user);

    // Maak een module aan met een geldige categorie uit de ENUM lijst in de migratie.
    // Categorie 'Veiligheid' is een geldige optie.
    $this->module = Module::factory()->create(['category' => 'Veiligheid']);

    // Explicit check of de module succesvol is aangemaakt.
    // Indien dit faalt, duidt het op een probleem met Module::factory() of de onderliggende setup.
    if (!$this->module instanceof Module) {
        throw new \RuntimeException('Module factory kon geen geldige Module instantie aanmaken. Controleer je ModuleFactory.php en database setup.');
    }

    // Zorg ervoor dat slots unieke indices hebben in elke testrun
    // Gebruik een statische teller of een random getal dat niet snel herhaald wordt.
    static $slotIndex = 0;
    $slotIndex++; // Verhoog de teller voor elke test

    // Maak een slot, event type en event aan die aan elkaar gekoppeld zijn.
    $this->slot = Slot::factory()->create([
        'module_id' => $this->module->id,
        'index' => $slotIndex // Gebruik een unieke index voor elke slot
    ]);
    $this->eventType = EventType::factory()->create();
    $this->event = Event::factory()->create([
        'slot_id' => $this->slot->id,
        'event_type_id' => $this->eventType->id,
        'end_time' => Carbon::now()->addHours(1),
        // Correctie: Gebruik 'recurring' in plaats van 'is_recurring' om overeen te komen met de migratie.
        'recurring' => false,
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
});

// Test: Het event dashboard wordt correct weergegeven.
test('it displays the event dashboard', function () {
    // Simuleer een GET-verzoek naar het event dashboard.
    // Aangepast van '/event-dashboard' naar '/events' om overeen te komen met web.php
    $response = $this->get('/events');

    // Controleer of de statuscode 200 is (OK).
    // De 302 redirect is waarschijnlijk een gevolg van de 500 error,
    // of een andere niet-afgehandelde redirect. We blijven 200 verwachten.
    $response->assertStatus(200);
    // Controleer of de correcte view wordt gebruikt.
    $response->assertViewIs('event_dashboard');
    // Controleer of alle verwachte variabelen aan de view zijn meegegeven.
    $response->assertViewHasAll(['event_types', 'slots', 'activeEvents', 'event_type_modules']);
});


// Test: Er wordt een fout geretourneerd als het event type niet gevonden wordt bij het instellen van een event.
test('it returns error if event type not found when setting event', function () {
    // Maak een nieuw, leeg slot aan.
    // Zorg voor een unieke index
    $newSlot = Slot::factory()->create([
        'module_id' => $this->module->id,
        'index' => $this->slot->index + 200 // Gebruik een unieke index
    ]);

    // Data voor het event met een niet-bestaand event type.
    $eventData = [
        'event_name' => 'Test Event',
        'event_description' => 'Een beschrijving voor test event',
        'event_type' => 'NietBestaandEventType', // Ongeldig event type
        'slot_id' => $newSlot->id,
        'duration' => 60,
        'duration_unit' => 'minutes',
        // Correctie: Gebruik 'recurring' in plaats van 'is_recurring' om overeen te komen met de migratie.
        'recurring' => false, // Aangepast
        'recurring_interval' => null,
        'recurring_unit' => null,
    ];

    // Simuleer het POST-verzoek.
    // Aangepast van '/events' naar '/events/set' om overeen te komen met web.php
    $response = $this->post('/events/set', $eventData);

    // Controleer of de redirect succesvol is.
    $response->assertRedirect();
    // Controleer of er een foutbericht in de sessie staat.
    // Aangepast op basis van mogelijke Engelstalige berichten
    $response->assertSessionHas('error', fn($message) => str_contains($message, 'Selected event type not found'));
    // Controleer of het event niet in de database is opgeslagen.
    $this->assertDatabaseMissing('events', ['name' => 'Test Event']);
});

// Test: Een event kan worden gereset voor een slot.
test('it can reset an event for a slot', function () {
    // Simuleer een POST-verzoek om het event van een slot te resetten.
    // Route '/events/reset' is correct zoals gedefinieerd in web.php
    $response = $this->post('/events/reset', ['slot_id' => $this->slot->id]);

    // Controleer of de redirect succesvol is.
    $response->assertRedirect();
    // Controleer of er een succesbericht in de sessie staat.
    // Aangepast op basis van mogelijke Engelstalige berichten
    $response->assertSessionHas('success', fn($message) => str_contains($message, 'Event for slot ' . $this->slot->id . ' has been reset to normal!'));

    // Controleer of het event uit de database is verwijderd.
    $this->assertDatabaseMissing('events', ['id' => $this->event->id]);
    // Controleer of de event_id van het slot op null is gezet.
    $this->assertDatabaseHas('slots', [
        'id' => $this->slot->id,
        'event_id' => null,
    ]);
});

// Test: Er wordt een fout geretourneerd als het slot niet gevonden wordt bij het resetten van een event.
test('it returns error if slot not found when resetting event', function () {
    // Simuleer een POST-verzoek met een niet-bestaand slot ID.
    // Route '/events/reset' is correct zoals gedefinieerd in web.php
    $response = $this->post('/events/reset', ['slot_id' => 9999]); // Niet-bestaand slot ID

    // Controleer of de redirect succesvol is.
    $response->assertRedirect();
    // In plaats van assertSessionHas('error'), controleren we op validatiefouten
    $response->assertInvalid('slot_id'); // Controleert of de 'slot_id' validatie is mislukt
});

// Test: Er wordt een fout geretourneerd als er geen event is ingesteld voor een slot bij het resetten.
test('it returns error if no event set for slot when resetting', function () {
    // Maak een slot aan zonder een gekoppeld event.
    // Zorg voor een unieke index
    $emptySlot = Slot::factory()->create([
        'module_id' => $this->module->id,
        'event_id' => null,
        'index' => $this->slot->index + 300 // Gebruik een unieke index
    ]);

    // Simuleer een POST-verzoek om het event van dit lege slot te resetten.
    // Route '/events/reset' is correct zoals gedefinieerd in web.php
    $response = $this->post('/events/reset', ['slot_id' => $emptySlot->id]);

    // Controleer of de redirect succesvol is.
    $response->assertRedirect();
    // Controleer of er een foutbericht in de sessie staat.
    // Aangepast op basis van mogelijke Engelstalige berichten
    $response->assertSessionHas('error', fn($message) => str_contains($message, 'No event set for slot ' . $emptySlot->id . '!'));
});

// Test: Alle actieve slot events kunnen via de API worden opgehaald.
test('it gets all active slot events via api', function () {
    // Simuleer een GET-verzoek naar de API om actieve slot events op te halen.
    // Aangepast van '/api/slot-events' naar '/events/slot-events' om overeen te komen met web.php
    $response = $this->get('/events/slot-events');

    // Controleer of de statuscode 200 is (OK).
    // De 302 redirect is waarschijnlijk een gevolg van de 500 error,
    // of een andere niet-afgehandelde redirect. We blijven 200 verwachten.
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
});

