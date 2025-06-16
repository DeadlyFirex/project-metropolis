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
    /**
     * Display the event dashboard with available slots and events.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        Log::info('EventController index method called.');

        // Retrieve all slots that are not disabled
        $slots = Slot::whereHas('module', function ($query) {
            $query->where('category', '!=', 'disabled');
        })->get();
        Log::debug('Total slots retrieved: ' . $slots->count());

        $event_types = $this->getAvailableEvents();
        Log::debug('Available event types fetched:', $event_types);

        $activeEvents = $this->getActiveSlotEvents();
        Log::debug('Active events fetched for dashboard:', $activeEvents);

        $event_type_modules = EventType::pluck('module_id', 'name');
        Log::debug('Event type modules fetched:', $event_type_modules->toArray());

        $this->cleanupExpiredEvents();

        return view('event_dashboard', compact('event_types',
            'slots', 'activeEvents', 'event_type_modules'));
    }

    /**
     * Set an event for a specific slot.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
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
            'is_recurring' => 'nullable|boolean',
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
            'name'               => $request->event_name,
            'description'        => $request->event_description,
            'start_time'         => $startTime,
            'end_time'           => $endTime,
            'is_recurring'       => $request->boolean('is_recurring'),
            'recurring_interval' => $request->filled('recurring_interval') ? (int)$request->recurring_interval : null,
            'recurring_unit'     => $request->filled('recurring_unit')   ? $request->recurring_unit          : null,
            'event_type_id'      => $eventType->id,
            'slot_id'            => $request->slot_id,
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

    /**
     * Reset the event for a specific slot.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * API endpoint to get all active slot events.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSlotEvents()
    {
        Log::info('getSlotEvents API endpoint called.');
        $activeEvents = $this->getActiveSlotEvents();
        Log::debug('Data sent to frontend via getSlotEvents:', $activeEvents);
        return response()->json($activeEvents);
    }

    /**
     * Retrieves all active slot events with their details.
     *
     * @return array
     */
    private function getActiveSlotEvents()
    {
        $activeEvents = [];
        $now = now();
        $processedEvents = [];

        $slots = Slot::with(['event.eventType.effects'])->get()->fresh();

        foreach ($slots as $slot) {
            $event = $slot->event;
            if (!$event || in_array($event->id, $processedEvents)) {
                continue;
            }

            if ($event->end_time && Carbon::parse($event->end_time)->isFuture()) {
                // Hoofd-evenement
                $eventEffects = $this->getEventEffects($event->event_type_id);
                $timeRemaining = $this->getRemainingTime($now, Carbon::parse($event->end_time));

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
                    'is_primary' => true
                ];

                $adjacentSlots = $this->getAdjacentSlots($slot->id);
                foreach ($adjacentSlots as $adjacentSlot) {
                    if (!isset($activeEvents[$adjacentSlot->id])) {
                        $adjacentEffects = $this->getAdjacentEventEffects($event->event_type_id);

                        if (!empty($adjacentEffects)) {
                            $activeEvents[$adjacentSlot->id] = [
                                'slot_id' => $adjacentSlot->id,
                                'event_id' => $event->id,
                                'event_name' => $event->name . ' (Aangrenzend)',
                                'description' => 'Aangrenzend effect van ' . $event->name,
                                'start_time' => $event->start_time,
                                'end_time' => $event->end_time,
                                'is_recurring' => (bool)$event->is_recurring,
                                'time_remaining' => $timeRemaining,
                                'effects' => $adjacentEffects,
                                'is_primary' => false,
                                'source_slot_id' => $slot->id // Voor referentie
                            ];
                        }
                    }
                }

                $processedEvents[] = $event->id;
            }
        }

        return $activeEvents;
    }

    /**
     * Calculates the end time based on the start time, duration, and unit.
     *
     * @param Carbon $startTime
     * @param int $duration
     * @param string $unit
     * @return Carbon
     */
    private function calculateEndTime($startTime, $duration, $unit)
    {
        Log::debug('Calculating end time:', ['start' => $startTime, 'duration' => (int)$duration, 'unit' => $unit]);
        return match ($unit) {
            'hours' => $startTime->copy()->addHours($duration),
            'days' => $startTime->copy()->addDays($duration),
            default => $startTime->copy()->addMinutes($duration),
        };
    }

    /**
     * Calculates the remaining time until the event ends.
     *
     * @param Carbon $now
     * @param Carbon $endTime
     * @return string
     */
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

    /**
     * Retrieves a list of available event types with their descriptions.
     *
     * @return array
     */
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

    /**
     * Retrieves the effects of a specific event type.
     *
     * @param int $eventTypeId
     * @return array
     */
    public function getEventEffects($eventTypeId)
    {
        $eventType = EventType::with('effects')->find($eventTypeId);
        if (!$eventType) return [];

        $effects = [
            'safety' => 0,
            'recreation' => 0,
            'climate' => 0,
            'facilities' => 0,
            'infrastructure' => 0
        ];

        foreach ($eventType->effects as $effect) {
            if (array_key_exists($effect->type, $effects)) {
                if ($effect->is_primary_effect || $effect->is_adjacent_effect) {
                    $effects[$effect->type] += $effect->value;
                }
            }
        }

        // Filter eventuele effecten met een waarde van 0 eruit
        return array_filter($effects, fn($value) => $value !== 0);
    }

    /**
     * API endpoint to get the effects of a specific event.
     *
     * @param Event $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEventEffectsApi(Event $event)
    {
        Log::debug("Fetching effects for event {$event->id}");

        $effects = $this->getEventEffects($event->event_type_id);

        return response()->json([
            'effects' => $effects
        ]);
    }

    /**
     * Retrieves adjacent slots for a given slot ID.
     *
     * @param int $slotId
     * @return \Illuminate\Support\Collection
     */
    public function getAdjacentSlots($slotId)
    {
        $gridWidth = 4;
        $gridHeight = 3;

        $slot = Slot::find($slotId);
        if (!$slot) {
            return collect();
        }

        $adjacentIds = [];
        $currentPos = $slotId;

        $row = ceil($currentPos / $gridWidth);
        $col = $currentPos % $gridWidth;
        if ($col == 0) $col = $gridWidth;
        if ($col > 1) $adjacentIds[] = $currentPos - 1;
        if ($col < $gridWidth) $adjacentIds[] = $currentPos + 1;
        if ($row > 1) $adjacentIds[] = $currentPos - $gridWidth;
        if ($row < $gridHeight) $adjacentIds[] = $currentPos + $gridWidth;

        return Slot::whereIn('id', $adjacentIds)->get();
    }

    /**
     * Retrieves the effects of adjacent events for a given event type ID.
     *
     * @param int $eventTypeId
     * @return array
     */
    public function getAdjacentEventEffects($eventTypeId)
    {
        $eventType = EventType::with('effects')->find($eventTypeId);
        if (!$eventType) return [];

        $effects = [
            'safety' => 0,
            'recreation' => 0,
            'climate' => 0,
            'facilities' => 0,
            'infrastructure' => 0
        ];

        foreach ($eventType->effects as $effect) {
            if ($effect->is_adjacent_effect) {
                $effects[$effect->type] = ($effects[$effect->type] ?? 0) + $effect->value;
            }
        }

        return array_filter($effects, fn($value) => $value !== 0);
    }

    /**
     * Clean up expired events and reschedule recurring ones so that the next occurrence is always in the future.
     */
    private function cleanupExpiredEvents(): void
    {
        $now = Carbon::now();

        Event::whereNotNull('end_time')
            ->where('end_time', '<=', $now)
            ->get()
            ->each(function (Event $event) use ($now) {
                // Recurring?
                if ($event->is_recurring && $event->recurring_interval && $event->recurring_unit) {

                    // Original duration in minutes
                    $durationMinutes = Carbon::parse($event->end_time)
                        ->diffInMinutes(Carbon::parse($event->start_time));

                    // Calculate the next start that lies **after** $now
                    $nextStart = Carbon::parse($event->start_time);
                    $nextEnd   = Carbon::parse($event->end_time);

                    do {
                        $nextStart = match ($event->recurring_unit) {
                            'hours'   => $nextStart->addHours($event->recurring_interval),
                            'days'    => $nextStart->addDays($event->recurring_interval),
                            default   => $nextStart->addMinutes($event->recurring_interval),
                        };
                        $nextEnd = $nextStart->copy()->addMinutes($durationMinutes);
                    } while ($nextEnd->lte($now));

                    // Persist the new schedule
                    $event->start_time = $nextStart;
                    $event->end_time   = $nextEnd;
                    $event->save();
                } else {
                    // One‑off event – detach from slot and delete.
                    $slot = Slot::where('event_id', $event->id)->first();
                    if ($slot) {
                        $slot->update(['event_id' => null]);
                    }
                    $event->delete();
                }
            });
    }
}
