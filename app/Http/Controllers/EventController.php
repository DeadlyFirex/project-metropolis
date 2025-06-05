<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Http\Request;
use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function index()
    {
        Log::info('EventController index method called.');

        $slots = Slot::all();
        Log::debug('Total slots retrieved: ' . $slots->count());

        $event_types = $this->getAvailableEvents();
        Log::debug('Available event types fetched:', $event_types);

        $activeEvents = $this->getActiveSlotEvents();
        Log::debug('Active events fetched for dashboard:', $activeEvents);

        $this->getAllCompatibleModules();

        return view('event_dashboard', compact('event_types', 'slots', 'activeEvents'));
    }

    public function setEvent(Request $request)
    {
        Log::info('setEvent method called with request data:', $request->all());

        $validatedData = $request->validate([
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

        Log::debug('Request validated successfully.');

        $startTime = now();
        $endTime = $this->calculateEndTime($startTime, (int)$request->duration, (string)$request->duration_unit);
        Log::debug('Calculated event times:', ['start_time' => $startTime, 'end_time' => $endTime]);

        $recurringNext = null;

        if ($request->boolean('is_recurring') && $request->filled('recurring_interval')) {
            $recurringInterval = (int)$request->recurring_interval;
            $recurringUnit = (string)$request->recurring_unit;

            $tempRecurringTime = $startTime->copy();
            $recurringNext = match ($recurringUnit) {
                'hours' => $tempRecurringTime->addHours($recurringInterval),
                'days' => $tempRecurringTime->addDays($recurringInterval),
                default => $tempRecurringTime->addMinutes($recurringInterval),
            };
            Log::debug('Recurring event details (next occurrence calculated but not stored):', [
                'interval' => $recurringInterval,
                'unit' => $recurringUnit,
                'next_end_time' => $this->calculateEndTime($recurringNext, (int)$request->duration, (string)$request->duration_unit)
            ]);
        } else {
            Log::debug('Event is not recurring.');
        }

        $eventType = EventType::where('name', $request->event_type)->first();
        if (!$eventType) {
            Log::error('EventType not found for name: ' . $request->event_type);
            return redirect()->back()->with('error', 'Selected event type not found!');
        }
        Log::debug('EventType found:', ['id' => $eventType->id, 'name' => $eventType->name]);

        $new_event = new Event([
            'name' => $request->event_name,
            'description' => $request->event_description,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_recurring' => $request->boolean('is_recurring'),
            'recurring_interval' => $request->filled('recurring_interval') ? (int) $request->recurring_interval : null,
            'event_type_id' => $eventType->id,
            'slot_id' => $request->slot_id,
        ]);
        $new_event->save();
        Log::info('New event created and saved:', $new_event->toArray());

        $slot = Slot::find($request->slot_id);
        if (!$slot) {
            Log::error('Slot not found during event assignment for ID: ' . $request->slot_id);
            $new_event->delete();
            return redirect()->back()->with('error', 'Slot not found during event assignment!');
        }
        $slot->event_id = $new_event->id;
        $slot->save();
        Log::info('Slot updated with new event_id:', ['slot_id' => $slot->id, 'event_id' => $new_event->id]);

        return redirect()->back()->with('success', 'Event successfully set for slot ' . $request->slot_id . '!');
    }

    public function resetEvent(Request $request)
    {
        Log::info('resetEvent method called for slot_id: ' . $request->slot_id);

        $request->validate([
            'slot_id' => 'required|exists:slots,id',
        ]);

        $slot = Slot::find($request->slot_id);
        if (!$slot) {
            Log::error('Slot not found during reset for ID: ' . $request->slot_id);
            return redirect()->back()->with('error', 'Slot not found!');
        }

        $event = $slot->event;
        if (!$event) {
            Log::warning('No event found to reset for slot_id: ' . $request->slot_id);
            return redirect()->back()->with('error', 'No event set for slot ' . $request->slot_id . '!');
        }
        Log::debug('Found event to reset:', $event->toArray());

        $slot->event_id = null;
        $slot->save();
        Log::info('Slot event_id reset to null:', ['slot_id' => $slot->id]);

        $event->delete();
        Log::info('Event deleted:', ['event_id' => $event->id, 'event_name' => $event->name]);

        return redirect()->back()->with('success', 'Event for slot ' . $request->slot_id . ' has been reset to normal!');
    }

    public function getSlotEvents()
    {
        Log::info('getSlotEvents API endpoint called.');
        $activeEvents = $this->getActiveSlotEvents();
        Log::debug('Data sent to frontend via getSlotEvents:', $activeEvents);
        return response()->json($activeEvents);
    }

    private function getActiveSlotEvents()
    {
        $activeEvents = [];
        $now = now();
        Log::debug('Current time for active event check: ' . $now);

        $slots = Slot::with(['event.eventType.effects'])->get()->fresh();
        Log::debug('Total slots with eager loaded events (after fresh): ' . $slots->count());

        foreach ($slots as $slot) {
            Log::debug('Checking slot ' . $slot->id . '. Raw event_id: ' . ($slot->event_id ?? 'NULL'));

            $event = $slot->event;
            if ($event) {
                Log::debug('Processing slot ' . $slot->id . '. Associated event: ' . $event->name . ' (ID: ' . $event->id . ')');

                if ($event->end_time && Carbon::parse($event->end_time)->isFuture()) {
                    $timeRemaining = $this->getRemainingTime($now, Carbon::parse($event->end_time));
                    Log::debug('Event ' . $event->name . ' (ID: ' . $event->id . ') is active. Time remaining: ' . $timeRemaining);

                    $eventEffects = $this->getEventEffects($event->event_type_id);
                    Log::debug('Fetched effects for event type ' . $event->event_type_id . ':', $eventEffects);

                    $activeEvents[$slot->id] = [
                        'slot_id' => $slot->id,
                        'event_id' => $event->id,
                        'event_name' => $event->name,
                        'description' => $event->description,
                        'start_time' => $event->start_time,
                        'end_time' => $event->end_time,
                        'is_recurring' => (bool)$event->is_recurring,
                        'time_remaining' => $timeRemaining,
                        'effects' => $eventEffects,
                    ];
                } else if ($event->end_time && Carbon::parse($event->end_time)->isPast()) {
                    Log::info('Event ' . $event->name . ' (ID: ' . $event->id . ') for slot ' . $slot->id . ' has expired. End time: ' . $event->end_time);
                } else {
                    Log::debug('Event ' . $event->name . ' (ID: ' . $event->id . ') for slot ' . $slot->id . ' is not active (no end_time or not future).');
                }
            } else if ($slot->event_id && !empty($slot->event_id)) {
                Log::error('DEBUG_ERROR: Slot ' . $slot->id . ' has event_id ' . $slot->event_id . ' but the associated Event model could not be loaded via the relationship. Check Slot and Event model relationships and database integrity. Is Event ID ' . $slot->event_id . ' missing from the "events" table?');
            } else {
                Log::debug('Slot ' . $slot->id . ' has no associated event based on relationship (event_id: ' . ($slot->event_id ?? 'NULL') . ').');
            }
        }
        Log::info('Finished processing active events. Total active events found: ' . count($activeEvents));
        return $activeEvents;
    }

    private function calculateEndTime($startTime, $duration, $unit)
    {
        Log::debug('Calculating end time:', ['start' => $startTime, 'duration' => (int)$duration, 'unit' => $unit]);
        return match ($unit) {
            'hours' => $startTime->copy()->addHours($duration),
            'days' => $startTime->copy()->addDays($duration),
            default => $startTime->copy()->addMinutes($duration),
        };
    }

    private function getRemainingTime($now, $endTime)
    {
        $diff = $now->diff($endTime);
        Log::debug('Calculating remaining time diff:', ['now' => $now, 'end' => $endTime, 'diff' => $diff->format('%y years, %m months, %d days, %h hours, %i minutes, %s seconds')]);

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
        Log::debug('Available events list:', $eventList);
        return $eventList;
    }

    private function getAllCompatibleModules()
    {
        Log::info('getAllCompatibleModules method called.');
        $event_types = EventType::all();
        if ($event_types->isEmpty()) {
            Log::warning('No EventTypes found in getAllCompatibleModules.');
            return;
        }

        foreach ($event_types as $event_type) {
            $compatible_module = $event_type->compatible;

            if ($compatible_module) {
                Log::debug('Compatible module found for event type "' . $event_type->name . '":', $compatible_module->toArray());
            } else {
                Log::debug('No compatible module found for event type: ' . $event_type->name);
            }
        }
        Log::info('Finished checking all compatible modules.');
    }

    /**
     * Haal effecten op voor een event type
     */
    private function getEventEffects($eventTypeId)
    {
        $eventType = EventType::with('effects')->find($eventTypeId);

        if (!$eventType) {
            Log::error("EventType not found for ID: {$eventTypeId}");
            return [];
        }

        $effects = [
            'safety' => 0,
            'recreation' => 0,
            'climate' => 0,
            'facilities' => 0,
            'infrastructure' => 0
        ];

        foreach ($eventType->effects as $effect) {
            if (array_key_exists($effect->type, $effects)) {
                $effects[$effect->type] += $effect->value;
            }
        }

        // Filter out zero values
        return array_filter($effects, fn($value) => $value !== 0);
    }
    /**
     * API endpoint om effecten voor een specifiek event op te halen
     */
    public function getEventEffectsApi(Event $event)
    {
        Log::debug("Fetching effects for event {$event->id}");

        $effects = $this->getEventEffects($event->event_type_id);

        return response()->json([
            'effects' => $effects
        ]);
    }
}
