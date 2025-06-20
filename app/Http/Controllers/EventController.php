<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Http\Request;
use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Clock;

class EventController extends Controller
{
    /**
     * Display the event dashboard with available slots and events.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        Log::info('EventController index method called.');

        // Haal simulatie tijd op uit request (voor AJAX calls)
        $simTime = Clock::where('user_id', auth()->id())->value('time');
        Log::info('Simulation time parameter received', ['time' => $simTime]);

        // Retrieve all slots that are not disabled
        $slots = Slot::whereHas('module', function ($query) {
            $query->where('category', '!=', 'disabled');
        })->get();
        Log::debug('Total slots retrieved: ' . $slots->count());

        $event_types = $this->getAvailableEvents();
        Log::debug('Available event types fetched:', $event_types);

        // FIXED: Altijd cleanup doen, ook bij simulatie tijd
        $this->cleanupExpiredEvents($simTime);

        // Fetch all events for management, regardless of active status
        // We'll order them for better readability, e.g., by start_time
        $allEvents = Event::with('slot')->orderBy('start_time')->get()->groupBy('slot_id');
        Log::debug('All events fetched for dashboard:', $allEvents->toArray());


        // FIXED: Gebruik simulatie tijd consistent
        $activeEvents = $this->getSlotEventsForDashboard($simTime);
        Log::debug('Active events fetched for dashboard:', $activeEvents);

        $event_type_modules = EventType::pluck('module_id', 'name');
        Log::debug('Event type modules fetched:', $event_type_modules->toArray());

        return view('event_dashboard', compact(
            'slots',
            'event_types',
            'activeEvents',
            'allEvents', // Pass all events to the view
            'event_type_modules'
        ));
    }

    /**
     * Koppel één event aan één slot.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setEvent(Request $request)
    {
        // Debug: Log alle binnenkomende request data
        Log::info('setEvent method called with data:', $request->all());

        try {
            /* ───── 1. VALIDATIE ─────────────────────────────────────────── */
            $data = $request->validate([
                'event_name'        => 'required|string|max:255',
                'event_description' => 'nullable|string|max:255',
                'event_type'        => [
                    'required',
                    'string',
                    Rule::exists('event_types', 'name'),
                ],
                'slot_id'      => [
                    'required',
                    'integer',
                    Rule::exists('slots', 'id'),
                    function ($attribute, $value, $fail) {
                        if (Slot::where('id', $value)->whereNotNull('event_id')->exists()) {
                            $fail('Dit vakje heeft al een event. Verwijder het eerst.');
                        }
                    },
                ],
                'start_time'   => 'required|date_format:H:i',
                'end_time'     => 'required|date_format:H:i|after:start_time',
                'is_recurring' => 'sometimes|boolean',
            ]);

            Log::info('Validation passed successfully:', $data);

            /* ───── 2. TIJDEN VERWERKEN ─────────────────────────────────── */
            $simTime = Clock::where('user_id', auth()->id())->value('time');
            $today = Carbon::createFromFormat('H:i:s', $simTime)->startOfDay();

            $start = Carbon::createFromFormat('H:i', $data['start_time'])
                ->setDate($today->year, $today->month, $today->day);

            $end = Carbon::createFromFormat('H:i', $data['end_time'])
                ->setDate($today->year, $today->month, $today->day);

            // Als eindtijd voor starttijd ligt, loopt event over middernacht
            if ($end->lte($start)) {
                $end->addDay();
            }

            // Voor niet-terugkerende events: als eindtijd in verleden ligt, schuif naar morgen
            $isRecurring = $request->boolean('is_recurring');
            $now = Carbon::createFromFormat('H:i:s', $simTime);
            if (!$isRecurring && $end->lte($now)) {
                $start->addDay();
                $end->addDay();
            }

            /* ───── 3. EVENT-TYPE OPHALEN ───────────────────────────────── */
            $eventType = EventType::where('name', $data['event_type'])->first();

            if (!$eventType) {
                Log::error('EventType not found:', ['event_type' => $data['event_type']]);
                return back()->withErrors(['event_type' => 'Geselecteerd event type bestaat niet.'])
                    ->withInput();
            }

            Log::info('EventType found:', ['id' => $eventType->id, 'name' => $eventType->name]);

            /* ───── 4. DATABASE TRANSACTIE ─────────────────────────────── */
            $event = null;

            DB::transaction(function () use ($data, $start, $end, $eventType, $isRecurring, &$event) {
                // Slot ophalen en vergrendelen
                $slot = Slot::lockForUpdate()->find($data['slot_id']);

                if (!$slot) {
                    throw new \Exception("Slot {$data['slot_id']} niet gevonden");
                }

                // Dubbel-check: heeft slot inmiddels een event gekregen?
                if ($slot->event_id) {
                    throw new \Exception("Slot {$data['slot_id']} heeft inmiddels al een event");
                }

                Log::info('Slot found and locked:', ['slot_id' => $slot->id]);

                // Event aanmaken
                $eventData = [
                    'name'          => $data['event_name'],
                    'description'   => $data['event_description'] ?? '',
                    'start_time'    => $start->format('H:i:s'),
                    'end_time'      => $end->format('H:i:s'),
                    'is_recurring'  => $isRecurring,
                    'event_type_id' => $eventType->id,
                    'slot_id'       => $slot->id,
                ];

                Log::info('Creating event with data:', $eventData);

                $event = Event::create($eventData);

                if (!$event) {
                    throw new \Exception('Event kon niet worden aangemaakt');
                }

                Log::info('Event created successfully:', ['event_id' => $event->id]);

                // Slot bijwerken met event_id
                $slot->update(['event_id' => $event->id]);

                Log::info('Slot updated with event_id:', [
                    'slot_id' => $slot->id,
                    'event_id' => $event->id
                ]);
            });

            /* ───── 5. SUCCESS RESPONSE ─────────────────────────────────── */
            $successMessage = "Event '{$data['event_name']}' succesvol ingesteld voor vakje {$data['slot_id']}!";

            Log::info('setEvent completed successfully:', [
                'event_id' => $event->id,
                'slot_id' => $data['slot_id'],
                'message' => $successMessage
            ]);

            return back()->with('success', $successMessage);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed in setEvent:', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Error in setEvent:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

            return back()->withErrors(['general' => 'Er is een fout opgetreden bij het instellen van het event: ' . $e->getMessage()])
                ->withInput();
        }
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
    public function getSlotEvents(Request $request)
    {
        $simTime = $request->query('time')
            ?: Clock::where('user_id', auth()->id())
                ->value('time')
                ?: now()->format('H:i:s');

        if (preg_match('/^\d{2}:\d{2}$/', $simTime)) {
            $simTime .= ':00';
        }

        $nowSec = $this->timeToSeconds($simTime);

        $active = Event::whereHas('slot')->get()
            ->filter(function($event) use ($nowSec) {
            $startSec = $this->timeToSeconds($event->start_time);
            $endSec = $this->timeToSeconds($event->end_time);

            if ($endSec <= $startSec) $endSec += 24 * 3600;

            $nowAdj = $nowSec;
            if ($nowAdj < $startSec) $nowAdj += 24 * 3600;

            // Skip expired non-recurring events
            if (!$event->is_recurring && $nowAdj > $endSec) {
                return false;
            }

            return ($nowAdj >= $startSec && $nowAdj <= $endSec);
        })->load(['eventType', 'slot'])
            ->values();

        return response()->json($active);
    }


    /**
     * Retrieves all active slot events with their details.
     *
     * @return array
     */
    private function getActiveSlotEvents(?string $simTime = null): array
    {
        // Gebruik simulatie tijd of val terug op echte tijd
        $now = $simTime ?
            Carbon::createFromFormat('H:i:s', $simTime)->setDate(Carbon::today()->year, Carbon::today()->month, Carbon::today()->day) :
            now();

        $activeEvents = [];
        $processed    = [];

        $slots = Slot::with(['event.eventType.effects'])->get()->fresh();

        foreach ($slots as $slot) {
            $event = $slot->event;
            if (!$event || in_array($event->id, $processed)) continue;

            // Vandaag start/eind baseren op opgeslagen tijden
            $start = Carbon::parse($event->start_time)
                ->setDate($now->year, $now->month, $now->day);

            $end = Carbon::parse($event->end_time)
                ->setDate($now->year, $now->month, $now->day);

            if ($end->lte($start)) $end->addDay();

            // Eenmalig event voorbij? skip
            if (!$event->is_recurring && $now->gt($end)) {
                $processed[] = $event->id;
                continue;
            }

            // Is klok tussen start & end?
            if ($now->between($start, $end)) {
                $timeRemaining = $this->formatRemainingTime($now, $end);
                $effects       = $this->getEventEffects($event->event_type_id);

                $activeEvents[$slot->id] = [
                    'slot_id'        => $slot->id,
                    'event_id'       => $event->id,
                    'event_name'     => $event->name,
                    'description'    => $event->description,
                    'start_time'     => $event->start_time,
                    'end_time'       => $event->end_time,
                    'is_recurring'   => (bool) $event->is_recurring,
                    'time_remaining' => $timeRemaining,
                    'effects'        => $effects,
                    'is_primary'     => true,
                ];

                // Adjacent vakjes
                foreach ($this->getAdjacentSlots($slot->id) as $adjacent) {
                    if (isset($activeEvents[$adjacent->id])) continue;

                    $adjacentEffects = $this->getAdjacentEventEffects($event->event_type_id);
                    if (empty($adjacentEffects)) continue;

                    $activeEvents[$adjacent->id] = [
                        'slot_id'        => $adjacent->id,
                        'event_id'       => $event->id,
                        'event_name'     => $event->name . ' (Aangrenzend)',
                        'description'    => 'Aangrenzend effect van ' . $event->name,
                        'start_time'     => $event->start_time,
                        'end_time'       => $event->end_time,
                        'is_recurring'   => (bool) $event->is_recurring,
                        'time_remaining' => $timeRemaining,
                        'effects'        => $adjacentEffects,
                        'is_primary'     => false,
                        'source_slot_id' => $slot->id,
                    ];
                }
            }

            $processed[] = $event->id;
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
    private function cleanupExpiredEvents(?string $simTime = null): void
    {
        $currentTimeStr = $simTime ?? date('H:i:s');
        $nowSec = $this->timeToSeconds($currentTimeStr);

        Event::all()->each(function (Event $event) use ($nowSec) {
            $startTime = is_string($event->start_time) ? $event->start_time : $event->start_time->format('H:i:s');
            $endTime = is_string($event->end_time) ? $event->end_time : $event->end_time->format('H:i:s');

            $startSec = $this->timeToSeconds($startTime);
            $endSec = $this->timeToSeconds($endTime);

            if ($endSec <= $startSec) {
                $endSec += 24 * 3600;
            }

            // For non-recurring events: delete if expired
            if (!$event->is_recurring && $endSec <= $nowSec) {
                Slot::where('event_id', $event->id)->update(['event_id' => null]);
                $event->delete();
                Log::info("Expired non-recurring event deleted: {$event->id}");
            }
            // For recurring events: shift to next day if expired
            elseif ($event->is_recurring && $endSec <= $nowSec) {
                $newStartSec = ($startSec + 24 * 3600) % (24 * 3600);
                $newEndSec = ($endSec + 24 * 3600) % (24 * 3600);

                $event->start_time = gmdate('H:i:s', $newStartSec);
                $event->end_time = gmdate('H:i:s', $newEndSec);
                $event->save();

                Log::info("Recurring event rescheduled: {$event->id} to {$event->start_time}-{$event->end_time}");
            }
        });
    }



    /* ---------- formatRemainingTime() ---------- */
    private function formatRemainingTime(Carbon $now, Carbon $end): string
    {
        $diff = $now->diff($end);
        return $diff->days  > 0 ? $diff->days.' dag(en), '.$diff->h.' uur'
            : ($diff->h    > 0 ? $diff->h.' uur, '.$diff->i.' min'
                :                    $diff->i.' minuten');
    }

    public function getSlotEventsForDashboard(?string $simTime = null): array
    {
        // Gebruik altijd de simulatie tijd als die beschikbaar is
        $currentTime = $simTime ?? date('H:i:s');
        $now = Carbon::createFromFormat('H:i:s', $currentTime)
            ->setDate(Carbon::today()->year, Carbon::today()->month, Carbon::today()->day);

        $items = [];

        Slot::with(['event.eventType.effects', 'module'])->get()->each(function ($slot) use (&$items, $now) {
            $event = $slot->event;
            if (!$event) {
                return;
            }

            $start = Carbon::createFromFormat('H:i:s', $event->start_time)
                ->setDate($now->year, $now->month, $now->day);

            $end = Carbon::createFromFormat('H:i:s', $event->end_time)
                ->setDate($now->year, $now->month, $now->day);

            // Handle overnight events
            if ($end->lte($start)) {
                $end->addDay();
            }

            // For non-recurring events: skip if expired
            if (!$event->is_recurring && $now->gt($end)) {
                return;
            }

            // Bepaal status en welke tijd we gaan aftellen
            if ($now->between($start, $end)) {
                // Event is nu bezig
                $status      = 'running';
                $displayTime = $end;
            } else {
                // Event is gepland (of recurring ná vandaag)
                $status      = 'scheduled';
                $displayTime = $start;

                if ($event->is_recurring && $now->gt($end)) {
                    // Voor recurring events: show start morgen
                    $displayTime = $start->copy()->addDay();
                }
            }

            $items[$slot->id] = [
                'slot_id'        => $slot->id,
                'event_id'       => $event->id,
                'event_name'     => $event->name,
                'description'    => $event->description,
                'start_time'     => $event->start_time,
                'end_time'       => $event->end_time,
                'is_recurring'   => (bool)$event->is_recurring,
                'time_remaining' => $this->formatRemainingTime($now, $displayTime),
                'effects'        => $this->getEventEffects($event->event_type_id),
                'status'         => $status,
            ];
        });

        return $items;
    }



    /**
     * Zet "HH:MM:SS" om naar seconden sinds middernacht.
     */
    private function timeToSeconds(string $time): int
    {
        [$h, $m, $s] = explode(':', $time);
        return ((int)$h * 3600) + ((int)$m * 60) + (int)$s;
    }

    /**
     * Formatteer een aantal seconden als "X uur, Y min" of "Z minuten".
     */
    private function formatRemainingTimeSec(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);

        if ($h > 0) {
            return "{$h} uur, {$m} min";
        }
        return "{$m} minuten";
    }

    public function getActiveEventsForSimulation(Request $request)
    {
        $simTime = Clock::where('user_id', auth()->id())->value('time');

        // Cleanup verlopen events
        $this->cleanupExpiredEvents($simTime);

        // Haal actieve events op
        $activeEvents = $this->getSlotEventsForDashboard($simTime);

        return response()->json($activeEvents);
    }


}
