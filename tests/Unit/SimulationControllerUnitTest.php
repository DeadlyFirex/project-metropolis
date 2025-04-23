<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\SimulationController;
use App\Models\Module;
use App\Models\Slot;
use ReflectionClass;

/**
 * Unit tests for SimulationController private methods.
 *
 * Ensures getAllSlots returns all Slot records,
 * and getModules returns modules correctly filtered by category.
 */
class SimulationControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to invoke private methods via reflection.
     */
    private function callPrivateMethod($object, string $methodName, array $args = [])
    {
        $ref = new ReflectionClass($object);
        $method = $ref->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }

    /**
     * Test that getAllSlots returns all slots from the database.
     */
    public function testGetAllSlotsReturnsAllSlots()
    {
        $slots = Slot::factory()->count(3)->create();
        $controller = new SimulationController();
        $result = $this->callPrivateMethod($controller, 'getAllSlots');
        $this->assertCount(3, $result);
        $this->assertEqualsCanonicalizing(
            $slots->pluck('id')->all(),
            $result->pluck('id')->all()
        );
    }

    /**
     * Test that getModules with no category returns all modules.
     */
    public function testGetModulesWithoutCategoryReturnsAllModules()
    {
        $modules = Module::factory()->count(4)->create();
        $controller = new SimulationController();
        $result = $this->callPrivateMethod($controller, 'getModules', [null]);
        $this->assertCount(4, $result);
        $this->assertEqualsCanonicalizing(
            $modules->pluck('id')->all(),
            $result->pluck('id')->all()
        );
    }

    /**
     * Test that getModules with a specific category
     * returns only modules belonging to that category.
     */
    public function testGetModulesWithCategoryReturnsFilteredModules()
    {
        Module::factory()->create(['category' => 'catA']);
        Module::factory()->create(['category' => 'catB']);
        Module::factory()->create(['category' => 'catA']);
        $controller = new SimulationController();
        $result = $this->callPrivateMethod($controller, 'getModules', ['catA']);
        $this->assertCount(2, $result);
        foreach ($result as $module) {
            $this->assertEquals('catA', $module->category);
        }
    }
}
