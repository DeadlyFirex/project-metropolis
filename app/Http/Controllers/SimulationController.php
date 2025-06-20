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
use App\Models\Clock;
use App\Models\Feedback;


class SimulationController extends Controller
{
    public function index(Request $request)
    {
        // Get current category
        $category     = $request->input('category');
        $modules      = $this->getModules($category);

        // Overall information
        $categories   = Module::select('category')->distinct()->pluck('category');
        $all_modules  = $this->getModules();
        $slots        = Slot::with(['module.effects'])->get();
        $events       = Event::all();
        $feedback = Feedback::latest()->get();

        // Get clock time and date
        $clockTime = Clock::where('user_id', Auth::id())
            ->value('time') ?? now()->format('H:i:s');
        $clockDate = Clock::where('user_id', Auth::id())
            ->value('date') ?? now()->format('Y-m-d');

        // ?
        $nextExpiration = Event::whereNotNull('end_time')
            ->where('end_time', '>', now())
            ->min('end_time');

        return view('sim_dashboard', compact(
            'modules',
            'category',
            'categories',
            'slots',
            'all_modules',
            'events',
            'clockTime',
            'clockDate',
            'nextExpiration',
            'feedback'
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

    private function slotIsActive(Event $event, Carbon $clock): bool
    {
        $start = $event->start_time->copy()->setDate($clock->year, $clock->month, $clock->day);
        $end   = $event->end_time  ->copy()->setDate($clock->year, $clock->month, $clock->day);
        if ($end->lte($start)) $end->addDay();

        return $clock->between($start, $end);
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
