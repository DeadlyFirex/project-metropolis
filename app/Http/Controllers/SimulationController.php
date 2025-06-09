<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Facades\Validator;
use App\Models\Module;
use App\Models\Slot;
use App\Models\Effect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SimulationController extends Controller
{
    public function index(Request $request)
    {
        $category     = $request->input('category');
        $all_modules  = $this->getModules();
        $modules      = $this->getModules($category);
        $categories   = Module::select('category')->distinct()->pluck('category');
        $slots        = Slot::with(['module.effects'])->get();
        $events       = Event::all();

        return view('sim_dashboard', compact(
            'modules', 'category', 'categories', 'slots', 'all_modules', 'events'
        ));
    }

    private function getModules($category = null)
    {
        $query = Module::with('effects');
        if ($category) {
            $query->where('category', $category);
        }
        return $query->get();
    }

    public function koppelModule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:modules,id',
            'slot_id'   => 'required|exists:slots,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        $newModule  = Module::findOrFail($request->module_id);
        $targetSlot = Slot::findOrFail($request->slot_id);
        $conditions = app(ConditionsController::class);

        if ($conditions->violatesAdjacencyRule($newModule, $targetSlot)) {
            return response()->json([
                'message' => __('errors.category_incompatible', [
                    'category' => $newModule->category,
                ]),
            ], 422);
        }

        if ($conditions->exceedsCategoryLimit($newModule, $targetSlot)) {
            return response()->json([
                'message' => __('errors.category_limit_reached', [
                    'category' => $newModule->category,
                ]),
            ], 409);
        }

        $targetSlot->update(['module_id' => $newModule->id]);

        return response()->json(['success' => true]);
    }

    /**
     * Remove a module from a specific slot and associated event if any.
     *
     * @param Slot $slot
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeModule(Slot $slot)
    {
        Log::info('removeModule method called for slot_id: ' . $slot->id);

        // Check if there's an event associated with this slot
        if ($slot->event_id) {
            $event = Event::find($slot->event_id); // Find the event
            if ($event) {
                // Delete the event
                $event->delete();
                Log::info('Associated event deleted successfully: ' . $event->id);
            }
            // Reset the event_id on the slot
            $slot->event_id = null;
        }

        // Remove the module from the slot
        $slot->update(['module_id' => null]);

        Log::info('Module removed from slot ' . $slot->id . ' and event (if any) reset.');

        return redirect()->back()->with('success', 'Module en event (indien aanwezig) succesvol verwijderd!');
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
