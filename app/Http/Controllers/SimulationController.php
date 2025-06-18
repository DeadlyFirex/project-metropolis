<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Facades\Validator;
use App\Models\Module;
use App\Models\Slot;
use App\Models\Effect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
        $userId       = Auth::id();
        $userClock    = \App\Models\UserClock::where('user_id', $userId)->first();
        $clockTime    = $userClock ? $userClock->clock_time : '00:00:00';

        return view('sim_dashboard', compact(
            'modules',
            'category',
            'categories',
            'slots',
            'all_modules',
            'events',
            'clockTime'
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

        if ($targetSlot->approved) {
            return response()->json([
                'message' => 'Deze slot is goedgekeurd en kan niet meer gewijzigd worden.',
            ], 403);
        }

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

        // Blokkeer als slot is goedgekeurd
        if ($slot->approved) {
            Log::warning('Attempted to remove module from approved slot: ' . $slot->id);
            return redirect()->back()->with('error', 'Je kunt een goedgekeurde module niet verwijderen.');
        }

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
    public function saveClock(Request $request)
    {
        $request->validate([
            'time' => ['required', 'regex:/^\d{2}:\d{2}:\d{2}$/']
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $time = $request->input('time');
        $userClock = \App\Models\UserClock::updateOrCreate(
            ['user_id' => $userId],
            ['clock_time' => $time]
        );

        return response()->json(['success' => true]);
    }
    public function approve(Slot $slot)
    {
        $slot->approved = true;
        $slot->save();

        return redirect()->back()->with('success', 'Module is goedgekeurd.');
    }
    public function moveModule(Request $request)
    {
        $request->validate([
            'module_id' => 'required|integer|exists:modules,id',
            'from_slot_id' => 'required|integer|exists:slots,id',
            'to_slot_id' => 'required|integer|exists:slots,id',
        ]);

        $from = Slot::find($request->from_slot_id);
        $to = Slot::find($request->to_slot_id);

        if (!$from || !$to || $from->module_id != $request->module_id) {
            return response()->json(['message' => 'Ongeldige gegevens.'], 422);
        }

        if ($to->approved) {
            return response()->json([
                'message' => 'Dit slot is goedgekeurd en kan niet worden gewijzigd.'
            ], 403);
        }

        // Verwijder uit oude slot
        $from->module_id = null;
        $from->save();

        // Plaats in nieuwe slot
        $to->module_id = $request->module_id;
        $to->approved = false; // optioneel: opnieuw laten goedkeuren
        $to->save();

        return response()->json(['message' => 'Module verplaatst.']);
    }
}
