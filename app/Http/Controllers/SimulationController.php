<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Slot;
use App\Models\Effect;
use Illuminate\Http\Request;

class SimulationController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->input('category');
        $modules = $this->getModules($category);
        $categories = Module::select('category')->distinct()->pluck('category');
        $slots = Slot::with(['module.effects'])->get(); // preload relaties

        return view('sim_dashboard', compact('modules', 'category', 'categories', 'slots'));
    }

    private function getModules($category = null)
    {
        $query = Module::with('effects'); // preload effecten
        if ($category) {
            $query->where('category', $category);
        }
        return $query->get();
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
            return response()->json(['error' => 'Invalid effect type'], 400);
        }

        $validated = $request->validate([
            'value' => 'required|integer|min:-5|max:5',
        ]);

        $effect = Effect::updateOrCreate(
            ['module_id' => $moduleId, 'type' => $type],
            ['value' => $validated['value']]
        );

        return response()->json(['success' => true, 'effect' => $effect]);
    }
}
