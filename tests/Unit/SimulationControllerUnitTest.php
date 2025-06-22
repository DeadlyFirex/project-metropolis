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

}
