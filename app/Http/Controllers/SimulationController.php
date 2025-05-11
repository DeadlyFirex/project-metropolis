<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Module;
use App\Models\Slot;
use App\Models\Effect;

class SimulationController extends Controller
{
    public function index(Request $request){
        $category = $request->input('category');
        $modules = $this->getModules($category);
        $categories = Module::select('category')->distinct()->pluck('category');
        $slots = $this->getAllSlots();

        return view("sim_dashboard", compact('modules', 'category', 'categories', 'slots'));
    }

      private function getAllSlots() {
        return Slot::all();
      }

    private function getModules($category = null){
        if ($category) {
            return Module::where('category', $category)->get();
        }
        return Module::all();
    }

    public function koppelModule(Request $request)
{
    $request->validate([
        'module_id' => 'required|exists:modules,id',
        'slot_id' => 'required|exists:slots,id',
    ]);

    $slot = Slot::findOrFail($request->slot_id);
    $slot->module_id = $request->module_id;
    $slot->save();

    return response()->json(['success' => true]);
}

    public function removeModule(Slot $slot)
{
    $slot->update(['module_id' => null]);

    return redirect()->back();
    }
    
    public function updateEffect(Request $request, $moduleId, $type)
    {
        $validTypes = ['safety', 'recreation', 'climate', 'facilities', 'infrastructure'];
    
        if (!in_array($type, $validTypes)) {
            abort(400, 'Invalid effect type');
        }
    
        $validated = $request->validate([
            'value' => 'required|integer|min:-5|max:5'
        ]);
    
        $effect = Effect::updateOrCreate(
            ['module_id' => $moduleId, 'type' => $type],
            ['value' => $validated['value']]
        );
    
        return back();
    }
    
}    