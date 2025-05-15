<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request as HttpRequest;
use App\Http\Controllers\SimulationController;
use App\Models\Module;
use App\Models\Slot;
use ReflectionClass;

class SimulationControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    private function callPrivate(object $obj, string $method, array $args = [])
    {
        $ref    = new ReflectionClass($obj);
        $method = $ref->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    public function testGetAllSlotsReturnsAllSlots(): void
    {
        $slots = Slot::factory()->count(3)->create();
        $ctl   = new SimulationController();

        $result = $this->callPrivate($ctl, 'getAllSlots');

        $this->assertCount(3, $result);
        $this->assertEqualsCanonicalizing(
            $slots->pluck('id')->all(),
            $result->pluck('id')->all()
        );
    }

    public function testGetModulesWithoutCategoryReturnsAllModules(): void
    {
        $modules = Module::factory()->count(4)->create();
        $ctl     = new SimulationController();

        $result  = $this->callPrivate($ctl, 'getModules', [null]);

        $this->assertCount(4, $result);
        $this->assertEqualsCanonicalizing(
            $modules->pluck('id')->all(),
            $result->pluck('id')->all()
        );
    }

    public function testGetModulesWithCategoryReturnsFilteredModules(): void
    {
        Module::factory()->create(['category' => 'catA']);
        Module::factory()->create(['category' => 'catB']);
        Module::factory()->create(['category' => 'catA']);

        $ctl    = new SimulationController();
        $result = $this->callPrivate($ctl, 'getModules', ['catA']);

        $this->assertCount(2, $result);
        foreach ($result as $module) {
            $this->assertEquals('catA', $module->category);
        }
    }

    public function testLimitExceededReturns422(): void
    {
        $ctl = new SimulationController();

        $slot1 = Slot::factory()->create(['index' => 20]);
        $slot2 = Slot::factory()->create(['index' => 21]);

        $careA = Module::factory()->state(['category' => 'Care'])->create();
        $careB = Module::factory()->state(['category' => 'Care'])->create(); // limiet Care = 1

        $ctl->koppelModule(HttpRequest::create('/x', 'POST', [
            'module_id' => $careA->id,
            'slot_id'   => $slot1->id,
        ]));

        $resp = $ctl->koppelModule(HttpRequest::create('/x', 'POST', [
            'module_id' => $careB->id,
            'slot_id'   => $slot2->id,
        ]));

        $this->assertEquals(422, $resp->status());
    }

    public function testIncompatibleCategoriesReturn422(): void
    {
        $ctl = new SimulationController();

        $slotA = Slot::factory()->create(['index' => 30]);
        $slotB = Slot::factory()->create(['index' => 31]);

        $care   = Module::factory()->state(['category' => 'Care'])->create();
        $public = Module::factory()->state(['category' => 'Public Space'])->create();

        $ctl->koppelModule(HttpRequest::create('/x', 'POST', [
            'module_id' => $care->id,
            'slot_id'   => $slotA->id,
        ]));

        $resp = $ctl->koppelModule(HttpRequest::create('/x', 'POST', [
            'module_id' => $public->id,
            'slot_id'   => $slotB->id,
        ]));

        $this->assertEquals(422, $resp->status());
        $this->assertEquals(__('errors.category_incompatible'), $resp->getData()->message);
    }
}
