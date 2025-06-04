<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Http\Request;
use App\Models\Slot;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index()
    {
        $event_types = $this->getAvailableEvents();
        $slots = Slot::all();
        $activeEvents = $this->getActiveSlotEvents();

        return view('event_dashboard', compact('event_types', 'slots', 'activeEvents'));
    }

    public function setEvent(Request $request)
    {
        $request->validate([
            'event_name' => 'required|string|max:255',
            'event_description' => 'nullable|string|max:1000',
            'event_type' => 'required|string',
            'slot_id' => 'required|exists:slots,id',
            'duration' => 'required|integer|min:1',
            'duration_unit' => 'required|string|in:minutes,hours,days',
            'is_recurring' => 'nullable|boolean|default:false',
            'recurring_interval' => 'nullable|integer|min:1',
            'recurring_unit' => 'nullable|string|in:minutes,hours,days'
        ]);

        // TODO: Handle if validation fails, e.g., return error response

        // Calculate start and end times
        $startTime = now();
        $endTime = $this->calculateEndTime($startTime, (int)$request->duration, $request->duration_unit);
        $recurringTime = now();
        $recurringNext = null;

        // Handle recurring events, turn to seconds, int
        if ($request->boolean('is_recurring') && $request->recurring_interval) {
            $recurringInterval = (int)$request->recurring_interval;
            $recurringUnit = $request->recurring_unit;

            // Calculate the next start time based on the recurring interval
            $recurringTime = match ($recurringUnit) {
                'hours' => $recurringTime->addHours($recurringInterval),
                'days' => $recurringTime->addDays($recurringInterval),
                default => $recurringTime->addMinutes($recurringInterval),
            };
            // Recalculate end time for the recurring event
            $recurringNext = $this->calculateEndTime($recurringTime, (int)$request->duration, $request->duration_unit);
        }

        // TODO: Handle recurring events logic if needed, e.g., store next occurrence in session or database

        // Create or update the event in the database
        $new_event = new Event([
            'name' => $request->event_name,
            'slot_id' => $request->slot_id,
            'description' => $request->event_description,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_recurring' => $request->boolean('is_recurring'),
            'recurring_interval' => $request->recurring_interval ? (int) $request->recurring_interval : null, // Cast to int if present
            'event_type_id' => EventType::where('name', $request->event_type)->first()->id,
        ]);

        $new_event->save();

        // Assign to slot
        $slot = Slot::find($request->slot_id);
        $slot->event_id = $new_event->id;
        $slot->save();

        return redirect()->back()->with('success', 'Event is succesvol ingesteld voor vakje ' . $request->slot_id . '!');
    }

    public function resetEvent(Request $request)
    {
        $request->validate([
            'slot_id' => 'required|exists:slots,id',
        ]);

        // Check if the event exists in the database
        $event = Event::where('id', $request->slot_id)->first();
        if (!$event) {
            return redirect()->back()->with('error', 'Er is geen event ingesteld voor vakje ' . $request->slot_id . '!');
        }

        // Reset the slot event in the database
        $slot = Slot::find($request->slot_id);
        $slot->event_id = null;
        $slot->save();

        // Delete the event from the database
        $event->delete();

        return redirect()->back()->with('success', 'Event voor vakje ' . $request->slot_id . ' is teruggezet naar normaal!');
    }

    public function getSlotEvents()
    {
        return response()->json($this->getActiveSlotEvents());
    }

    private function getActiveSlotEvents()
    {
        $activeEvents = [];
        $now = now();

        // Eager load the event relation for all slots
        $slots = Slot::with('event')->get();

        foreach ($slots as $slot) {
            $event = $slot->event;
            if ($event && $event->end_time && Carbon::parse($event->end_time)->isFuture()) {
                $timeRemaining = $this->getRemainingTime($now, Carbon::parse($event->end_time));
                $activeEvents[$slot->id] = [
                    'slot_id' => $slot->id,
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'description' => $event->description,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                    'recurring' => $event->recurring,
                    'time_remaining' => $timeRemaining,
                ];
            }
        }

        return $activeEvents;
    }

    private function calculateEndTime($startTime, $duration, $unit)
    {
        // $duration is already cast to int in setEvent, but adding a defensive cast here
        // for robustness in case this method is called from elsewhere without validation
        return match ($unit) {
            'hours' => $startTime->copy()->addHours((int)$duration),
            'days' => $startTime->copy()->addDays((int)$duration),
            default => $startTime->copy()->addMinutes((int)$duration),
        };
    }

    private function getRemainingTime($now, $endTime)
    {
        $diff = $now->diff($endTime);

        if ($diff->days > 0) {
            return $diff->days . ' dag(en), ' . $diff->h . ' uur';
        }
        if ($diff->h > 0) {
            return $diff->h . ' uur, ' . $diff->i . ' min';
        }

        return $diff->i . ' minuten';
    }

    private function getAvailableEvents()
    {
        $events = EventType::all();
        $eventList = [];
        foreach ($events as $event) {
            $eventList[$event->name] = $event->description;
        }
        return $eventList;
    }
}
