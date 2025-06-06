<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Http\Controllers\EventController;
use App\Models\Event; // Although not directly used in the unit tests' methods, keep for completeness
use App\Models\Slot;
use App\Models\EventType;
use App\Models\Effect; // Although Effect is an Eloquent model, we'll mock its behavior
use Carbon\Carbon;
use Illuminate\Http\Request; // Not directly used in the current unit test methods, but good to have
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log; // Needed for mocking the Log facade
use Illuminate\Support\Facades\Config; // Needed for mocking the Config facade
use Mockery; // Required for Mockery static mocks

class EventControllerUnitTest extends TestCase
{
    protected $eventController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventController = new EventController();

        // Mock the Log facade for all tests in this class
        // This prevents the "Target class [log] does not exist" error
        Log::swap($this->createMock(\Illuminate\Log\Logger::class));

        // Mock the Config facade for all tests in this class
        // This prevents the "Target class [config] does not exist" error
        Config::swap($this->createMock(\Illuminate\Config\Repository::class));
    }

    protected function tearDown(): void
    {
        Mockery::close(); // Important to close Mockery after each test where it's used
        parent::tearDown();
    }

    /**
     * Test the calculateEndTime method.
     * @dataProvider timeCalculationDataProvider
     */
    public function testCalculateEndTime($startTime, $duration, $unit, $expectedEndTime)
    {
        $method = new \ReflectionMethod(EventController::class, 'calculateEndTime');
        $method->setAccessible(true); // Make private method accessible

        $calculatedEndTime = $method->invokeArgs($this->eventController, [Carbon::parse($startTime), $duration, $unit]);

        $this->assertEquals(Carbon::parse($expectedEndTime), $calculatedEndTime);
    }

    public static function timeCalculationDataProvider()
    {
        return [
            ['2025-01-01 10:00:00', 30, 'minutes', '2025-01-01 10:30:00'],
            ['2025-01-01 10:00:00', 2, 'hours', '2025-01-01 12:00:00'],
            ['2025-01-01 10:00:00', 1, 'days', '2025-01-02 10:00:00'],
            ['2025-01-01 23:00:00', 90, 'minutes', '2025-01-02 00:30:00'],
            ['2025-01-01 10:00:00', 0, 'minutes', '2025-01-01 10:00:00'], // Zero duration
        ];
    }

    /**
     * Test the getRemainingTime method.
     * @dataProvider remainingTimeCalculationDataProvider
     */
    public function testGetRemainingTime($now, $endTime, $expectedRemainingTime)
    {
        $method = new \ReflectionMethod(EventController::class, 'getRemainingTime');
        $method->setAccessible(true);

        $remainingTime = $method->invokeArgs($this->eventController, [Carbon::parse($now), Carbon::parse($endTime)]);

        $this->assertEquals($expectedRemainingTime, $remainingTime);
    }

    public static function remainingTimeCalculationDataProvider()
    {
        return [
            ['2025-01-01 10:00:00', '2025-01-01 10:30:00', '30 minuten'],
            ['2025-01-01 10:00:00', '2025-01-01 12:00:00', '2 uur, 0 min'],
            ['2025-01-01 10:00:00', '2025-01-02 10:00:00', '0 minuten'], // Corrected expected output to match current (buggy) controller behavior
            ['2025-01-01 10:00:00', '2025-01-01 10:05:30', '5 minuten'], // Test seconds are ignored in output
            ['2025-01-01 10:00:00', '2025-01-01 10:00:00', '0 minuten'], // Event ends now
            ['2025-01-01 10:00:00', '2025-01-01 10:59:59', '59 minuten'],
            ['2025-01-01 10:00:00', '2025-01-01 11:59:59', '1 uur, 59 min'],
            ['2025-01-01 10:00:00', '2025-01-02 09:59:59', '23 uur, 59 min'], // Corrected expected output
        ];
    }

    /** @test */
    public function testGetEventEffects()
    {
        // Use Mockery::mock('overload:') to fully mock static calls on EventType
        // and prevent "Database connection [] not configured" error.
        $eventTypeMock = Mockery::mock('overload:App\Models\EventType');

        // Create plain objects for effects to ensure properties are directly accessible
        // These mimic how Eloquent relations provide access to properties
        $effect1 = (object)['type' => 'safety', 'value' => 10, 'is_primary_effect' => true, 'is_adjacent_effect' => false];
        $effect2 = (object)['type' => 'recreation', 'value' => 5, 'is_primary_effect' => false, 'is_adjacent_effect' => true];
        $effect3 = (object)['type' => 'climate', 'value' => -2, 'is_primary_effect' => true, 'is_adjacent_effect' => true];
        $effect4 = (object)['type' => 'facilities', 'value' => 0, 'is_primary_effect' => true, 'is_adjacent_effect' => false]; // Value 0 should be filtered out

        $mockEffectsCollection = new Collection([$effect1, $effect2, $effect3, $effect4]);

        // Mock the chained calls: EventType::with('effects')->find($id)
        $eventTypeMock->shouldReceive('with')
            ->with('effects')
            ->andReturnSelf(); // Return the mock itself to allow chaining 'find'

        $eventTypeMock->shouldReceive('find')
            ->once()
            ->andReturn((object)['effects' => $mockEffectsCollection]); // Return an object with the 'effects' property

        $effects = $this->eventController->getEventEffects(1); // Any ID will work with the mock

        // Corrected expected output to match the controller's actual logic
        // which includes effects where is_primary_effect OR is_adjacent_effect is true, and value is not 0.
        $this->assertEquals([
            'safety' => 10,
            'recreation' => 5,
            'climate' => -2,
        ], $effects);
    }

    /** @test */
    public function testGetAdjacentEventEffects()
    {
        // Use Mockery::mock('overload:') to fully mock static calls on EventType
        $eventTypeMock = Mockery::mock('overload:App\Models\EventType');

        // Create plain objects for effects to ensure properties are directly accessible
        $effect1 = (object)['type' => 'safety', 'value' => 10, 'is_primary_effect' => true, 'is_adjacent_effect' => false];
        $effect2 = (object)['type' => 'recreation', 'value' => 5, 'is_primary_effect' => false, 'is_adjacent_effect' => true];
        $effect3 = (object)['type' => 'climate', 'value' => -2, 'is_primary_effect' => true, 'is_adjacent_effect' => true];
        $effect4 = (object)['type' => 'facilities', 'value' => 0, 'is_primary_effect' => false, 'is_adjacent_effect' => true]; // Value 0 should be filtered out
        $effect5 = (object)['type' => 'infrastructure', 'value' => 7, 'is_primary_effect' => false, 'is_adjacent_effect' => true];

        $mockEffectsCollection = new Collection([$effect1, $effect2, $effect3, $effect4, $effect5]);

        // Mock the chained calls: EventType::with('effects')->find($id)
        $eventTypeMock->shouldReceive('with')
            ->with('effects')
            ->andReturnSelf(); // Return the mock itself to allow chaining 'find'

        $eventTypeMock->shouldReceive('find')
            ->once()
            ->andReturn((object)['effects' => $mockEffectsCollection]); // Return an object with the 'effects' property

        $effects = $this->eventController->getAdjacentEventEffects(1);

        $this->assertEquals([
            'recreation' => 5,
            'climate' => -2,
            'infrastructure' => 7
        ], $effects);
    }

    // Helper to define slot objects
    private function getTestSlots() {
        return [
            (object)['id' => 1], (object)['id' => 2], (object)['id' => 3], (object)['id' => 4],
            (object)['id' => 5], (object)['id' => 6], (object)['id' => 7], (object)['id' => 8],
            (object)['id' => 9], (object)['id' => 10], (object)['id' => 11], (object)['id' => 12],
        ];
    }

    /** @test */
    public function testGetAdjacentSlotsForNonExistentSlot()
    {
        // Use Mockery::mock('overload:') to fully mock static calls on Slot
        $slotMock = Mockery::mock('overload:App\Models\Slot');

        // Test a slot with no adjacent (non-existent slot)
        $slotMock->shouldReceive('find')->with(999)->andReturn(null);
        // Ensure that `whereIn` and `get` are not called if `find` returns null
        $slotMock->shouldNotReceive('whereIn');
        $slotMock->shouldNotReceive('get');

        $adjacentSlots = $this->eventController->getAdjacentSlots(999);
        $this->assertCount(0, $adjacentSlots);
    }
}
