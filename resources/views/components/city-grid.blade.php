@php use Carbon\Carbon; @endphp
<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <div class="text-lg font-medium text-gray-800 dark:text-gray-200">
                <span id="clock" data-start="{{ $clockTime ?: '00:00:00' }}">00:00:00</span>
            </div>
            <div class="space-x-2 my-4">
    <button class="speed-button bg-blue-500 text-white px-2 py-1 text-sm sm:text-base rounded" data-speed="0.5">1X</button>
    <button class="speed-button bg-blue-500 text-white px-2 py-1 text-sm sm:text-base rounded" data-speed="1">2X</button>
    <button class="speed-button bg-blue-500 text-white px-2 py-1 text-sm sm:text-base rounded" data-speed="2">4X</button>
    <button class="speed-button bg-blue-500 text-white px-2 py-1 text-sm sm:text-base rounded" data-speed="5">10X</button>
    <button class="speed-button bg-blue-500 text-white px-2 py-1 text-sm sm:text-base rounded" data-speed="10">20X</button>
</div>
<!-- Pause/Resume Buttons -->
<div class="space-x-2 my-4">
    <button id="pause-button" class="bg-red-500 text-white px-3 py-1 rounded">Pause</button>
    <button id="resume-button" class="bg-green-500 text-white px-3 py-1 rounded hidden">Resume</button>
</div>
            <button id="toggle-mode-btn"
                    onclick="toggleDayNight()"
                    class="bg-blue-500 text-white px-5 py-2 rounded shadow text-base flex items-center gap-3">
                <span id="mode-icon" class="text-2xl">🌙</span>
                <span class="text-sm sm:text-base font-semibold">Modus</span>
            </button>


        </div>

        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Metropolis Grid</h2>

        <table class="table-auto border-collapse border border-gray-300 w-full text-center">
            <tbody>
            @foreach ($slots->chunk(4) as $row)
                <tr>
                    @foreach ($row as $slot)
                        <td class="border border-gray-300 p-4 w-[200px] h-[150px] bg-gray-100 align-middle text-center city-cell"
                            data-slot-id="{{ $slot->id }}" data-row="{{ $loop->parent->index }}"
                            data-col="{{ $loop->index }}">

                            <div class="city-slot flex flex-col items-center justify-center h-full relative"
                                 data-slot-id="{{ $slot->id }}"
                                 @if ($slot->module_id) data-module-id="{{ $slot->module_id }}" @endif
                                 @if ($slot->event
                                      && $slot->event->end_time
                                      && Carbon::parse($slot->event->end_time)->isFuture())
                                     data-event-id="{{ $slot->event_id }}"
                                 data-event-name="{{ $slot->event->name }}"
                                 data-event-image="{{ $slot->event->image_path ? asset('storage/'.$slot->event->image_path) : '' }}"
                                @endif

                            >

                                @if ($slot->module_id != null && $slot->module && $slot->module->image_path)
                                    <div class="relative flex flex-col items-center">
                                        <img src="{{ asset('storage/' . $slot->module->image_path) }}"
                                             alt="{{ $slot->module->name }}"
                                             class="w-[80px] h-[80px] object-contain pointer-events-none">

                                        <span class="text-xs text-gray-700">{{ $slot->module->name }}</span>

                                        <div class="grid-effects hidden text-[10px] mt-1 text-gray-600 text-center space-y-[1px]">
                                            @php
                                                $typeMap = [
                                                'safety' => 'Veiligheid',
                                                'recreation' => 'Recreatie',
                                                'climate' => 'Milieukwaliteit',
                                                'facilities' => 'Voorzieningen',
                                                'infrastructure' => 'Mobiliteit',
                                                ];
                                            @endphp

                                            @foreach ($slot->module->effects as $effect)
                                                @if ($effect->value !== 0)
                                                    <div class="effect" data-type="{{ $effect->type }}"
                                                         data-value="{{ $effect->value }}">
                                                        {{ $effect->value > 0 ? '+' : '' }}{{ $effect->value }}
                                                        {{ $typeMap[$effect->type] ?? $effect->type }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>

                                        <div class="combined-effects hidden absolute top-full mt-2 bg-white border text-[10px] text-gray-800 p-2 rounded shadow z-10">
                                        </div>

                                        <form method="POST" action="{{ route('slots.removeModule', $slot->id) }}"
                                              class="absolute top-0 right-0">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="bg-red-500 text-white rounded-full w-5 h-5 text-xs leading-none">
                                                ×
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">Leeg</span>
                                @endif

                                @if ($slot->event_id && $slot->event && $slot->event->eventType)
                                    <div class="event-effect-data hidden">
                                        @php
                                            $effects = $slot->event->eventType->effects ?? [];
                                        @endphp
                                        @foreach ($effects as $effect)
                                            @if ($effect->value != 0)
                                                <div class="effect-event"
                                                     data-type="{{ $effect->type }}"
                                                     data-value="{{ $effect->value }}"
                                                     data-is-primary-effect="{{ $effect->is_primary_effect ? 'true' : 'false' }}"
                                                     data-is-adjacent-effect="{{ $effect->is_adjacent_effect ? 'true' : 'false' }}">
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                            </div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    const clockEl = document.getElementById('clock');
    const startTimeStr = clockEl.dataset.start || '00:00:00';

    // Convert HH:MM:SS to total seconds
    function timeStrToSeconds(str) {
        const [h, m, s] = str.split(':').map(Number);
        return h * 3600 + m * 60 + s;
    }

    // Convert total seconds to HH:MM:SS string (24-hour wrap)
    function secondsToTimeStr(totalSeconds) {
        totalSeconds = Math.floor(totalSeconds) % (24 * 3600);  // wrap after 24h
        if (totalSeconds < 0) totalSeconds += 24 * 3600; // handle negative times just in case
        const h = Math.floor(totalSeconds / 3600);
        const m = Math.floor((totalSeconds % 3600) / 60);
        const s = totalSeconds % 60;
        return `${pad(h)}:${pad(m)}:${pad(s)}`;
    }

    function pad(num) {
        return num.toString().padStart(2, '0');
    }

    let currentSeconds = timeStrToSeconds(startTimeStr);
    let tickSpeed = 0.5; // seconds to add per tick
    let interval = null;
    let isPaused = false;

    function tickClock() {
        currentSeconds += tickSpeed;
        clockEl.innerText = secondsToTimeStr(currentSeconds);
    }

    function startClock() {
        if (interval) clearInterval(interval);
        interval = setInterval(tickClock, 1000);
    }

    function pauseClock() {
        if (interval) clearInterval(interval);
        isPaused = true;
        document.getElementById('pause-button').classList.add('hidden');
        document.getElementById('resume-button').classList.remove('hidden');
    }

    function resumeClock() {
        if (!isPaused) return;
        isPaused = false;
        startClock();
        document.getElementById('pause-button').classList.remove('hidden');
        document.getElementById('resume-button').classList.add('hidden');
    }

    // Speed buttons update tickSpeed but do not restart if paused
    document.querySelectorAll('.speed-button').forEach(button => {
        button.addEventListener('click', () => {
            tickSpeed = parseFloat(button.dataset.speed);
        });
    });

    document.getElementById('pause-button').addEventListener('click', pauseClock);
    document.getElementById('resume-button').addEventListener('click', resumeClock);

    startClock();

    function refreshGrid () {
        fetch("{{ route('events.slot-events') }}?time=" + currentTime)
            .then(r => r.json())
            .then(updateGrid)
            .catch(console.error);
    }

    function updateGrid (events) {
        document.querySelectorAll('.city-slot[data-event-id]').forEach(slot => {
            slot.removeAttribute('data-event-id');
            slot.removeAttribute('data-event-name');
            slot.removeAttribute('data-event-image');
            slot.querySelector('.event-badge')?.remove();
        });

        events.forEach(ev => {
            const slot = document.querySelector(`.city-slot[data-slot-id="${ev.slot_id}"]`);
            if (!slot) return;

            slot.dataset.eventId   = ev.id;
            slot.dataset.eventName = ev.name;
            if (ev.image_path) {
                slot.dataset.eventImage = ev.image_path.startsWith('http')
                    ? ev.image_path
                    : `{{ url('/') }}/${ev.image_path}`;
            }

        });
    }



    function checkAndApplyNightMode() {
        const hour = parseInt(currentTime.split(':')[0], 10);
        if (hour >= 0 && hour < 6) {
            document.body.classList.add('night-mode');
        } else {
            document.body.classList.remove('night-mode');
        }
    }

    setInterval(() => {
        tickClock();
        checkAndApplyNightMode();
        maybeSaveTime();
        refreshGrid();

    }, 1000);

    let lastSave = 0;

    function maybeSaveTime() {
        if (Date.now() - lastSave >= 15000) {
            saveTime();
            lastSave = Date.now();
        }
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function saveTime() {
        fetch('/save-clock', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                time: currentTime
            })
        }).catch(e => console.error('Tijd opslaan mislukt', e));
    }

    function toggleDayNight() {
        const hour = parseInt(currentTime.split(':')[0]);
        if (hour >= 6 && hour < 18) {
            currentTime = '00:00:00'; // Nacht
        } else {
            currentTime = '12:00:00'; // Dag
        }
        document.getElementById('clock').innerText = currentTime;
        checkAndApplyNightMode();
        refreshGrid();
        saveTime();
        updateModeIcon();
    }

    function updateModeIcon() {
        const icon = document.getElementById('mode-icon');

        // Voeg flip class toe voor animatie
        icon.classList.add('flipping');

        // Na animatie wissel emoji en verwijder flip class
        setTimeout(() => {
            if (document.body.classList.contains('night-mode')) {
                icon.textContent = '🌞';
            } else {
                icon.textContent = '🌙';
            }
            icon.classList.remove('flipping');
        }, 200); // iets korter dan transition zodat het net iets vloeiender voelt
    }



    document.addEventListener("DOMContentLoaded", () => {
        checkAndApplyNightMode(); // Initieel checken

        const cityCells = document.querySelectorAll('td.city-cell');
        const rows = 3;
        const cols = 4;

        function getCell(row, col) {
            if (row < 0 || row >= rows || col < 0 || col >= cols) return null;
            return document.querySelector(`.city-cell[data-row="${row}"][data-col="${col}"]`);
        }

        document.querySelector('tbody').addEventListener('mouseover', async (event) => {
            const slot = event.target.closest('.city-slot');
            if (!slot) return;

            const parentTd = slot.closest('td.city-cell');
            if (!parentTd) return;

            const row = parseInt(parentTd.dataset.row, 10);
            const col = parseInt(parentTd.dataset.col, 10);

            const currentSlot = slot;
            const hasCurrentModule = currentSlot && currentSlot.dataset.moduleId;

            // Clear previous highlights
            document.querySelectorAll('td.city-cell.bg-green-200').forEach(cell => {
                cell.classList.remove('bg-green-200');
            });

            // Highlight 3x3 area if current cell has module
            if (hasCurrentModule) {
                for (let r = row - 1; r <= row + 1; r++) {
                    for (let c = col - 1; c <= col + 1; c++) {
                        const cell = getCell(r, c);
                        if (cell) {
                            cell.classList.add('bg-green-200');
                        }
                    }
                }
            }

            // Calculate effects
            const eventEffects = {};
            const adjacentEventEffects = {};
            const moduleEffects = {};

            // Process all cells in 3x3 area
            for (let r = row - 1; r <= row + 1; r++) {
                for (let c = col - 1; c <= col + 1; c++) {
                    const cell = getCell(r, c);
                    if (!cell) continue;

                    const cellSlot = cell.querySelector('.city-slot');
                    if (!cellSlot) continue;

                    const isCurrentCell = (r === row && c === col);

                    // Process module effects (full value for all cells in 3x3 area)
                    if (cellSlot.dataset.moduleId) {
                        const effectElements = cellSlot.querySelectorAll('.grid-effects .effect');
                        effectElements.forEach(el => {
                            const type = el.dataset.type;
                            const value = parseInt(el.dataset.value, 10);
                            moduleEffects[type] = (moduleEffects[type] || 0) + value;
                        });
                    }

                    // Process event effects
                    if (cellSlot.dataset.eventId) {
                        const eventEffectElements = cellSlot.querySelectorAll('.event-effect-data .effect-event');
                        eventEffectElements.forEach(el => {
                            const type = el.dataset.type;
                            const value = parseInt(el.dataset.value, 10);
                            const isPrimary = el.dataset.isPrimaryEffect === 'true';
                            const isAdjacent = el.dataset.isAdjacentEffect === 'true';

                            if (isCurrentCell) {
                                // Current cell gets both primary and adjacent effects from its own event
                                if (isPrimary) {
                                    eventEffects[type] = (eventEffects[type] || 0) + value;
                                }
                            } else {
                                // Adjacent cells only contribute their adjacent effects
                                if (isAdjacent) {
                                    adjacentEventEffects[type] = (adjacentEventEffects[type] || 0) + value;
                                }
                            }
                        });
                    }
                }
            }

            // Generate tooltip content
            const typeMap = {
                'safety': 'Veiligheid',
                'recreation': 'Recreatie',
                'climate': 'Milieukwaliteit',
                'facilities': 'Voorzieningen',
                'infrastructure': 'Mobiliteit'
            };

            let html = '';

            // Event effects display
            if (slot.dataset.eventId) {
                html += `<div class="mb-2 pb-2 border-b border-gray-300">`;
                html += `<div class="font-bold text-gray-800 text-sm mb-1">Actief Evenement: ${slot.dataset.eventName}</div>`;
                if (slot.dataset.eventImage) {
                    html += `<img src="${slot.dataset.eventImage}" alt="${slot.dataset.eventName}" class="w-10 h-10 object-contain mx-auto mb-1">`;
                }
                if (Object.keys(eventEffects).length > 0) {
                    html += `<div class="font-semibold text-gray-700 text-xs mb-1">Evenement Effecten (deze cel):</div>`;
                    for (const type in eventEffects) {
                        const val = eventEffects[type];
                        const label = typeMap[type] || type;
                        const color = val > 0 ? 'text-green-600' : (val < 0 ? 'text-red-600' : 'text-gray-600');
                        html += `<div class="${color}">${val > 0 ? '+' : ''}${val} ${label}</div>`;
                    }
                } else {
                    html += `<div class="text-gray-500">Geen directe evenement effecten</div>`;
                }
                html += `</div>`;
            }

            // Adjacent event effects
            if (Object.keys(adjacentEventEffects).length > 0) {
                html += `<div class="mb-2 pb-2 border-b border-gray-300">`;
                html += `<div class="font-semibold text-gray-700 text-xs mt-2 mb-1">Aangrenzende evenementeffecten:</div>`;
                for (const type in adjacentEventEffects) {
                    const val = Math.round(adjacentEventEffects[type] * 10) / 10;
                    const label = typeMap[type] || type;
                    const color = val > 0 ? 'text-green-600' : (val < 0 ? 'text-red-600' : 'text-gray-600');
                    html += `<div class="${color}">${val > 0 ? '+' : ''}${val} ${label}</div>`;
                }
                html += `</div>`;
            } else if (slot.dataset.eventId) {
                html += `<div class="mb-2 pb-2 border-b border-gray-300">`;
                html += `<div class="text-gray-500">Geen aangrenzende evenementeffecten</div>`;
                html += `</div>`;
            }

            // Module effects
            html += `<div class="mb-2 pb-2 border-b border-gray-300">`;
            html += `<div class="font-bold text-gray-800 text-sm mb-1">Gezamenlijke Module Effecten (buurt):</div>`;
            if (Object.keys(moduleEffects).length > 0) {
                for (const type in moduleEffects) {
                    const val = moduleEffects[type];
                    const label = typeMap[type] || type;
                    const color = val > 0 ? 'text-green-600' : (val < 0 ? 'text-red-600' : 'text-gray-600');
                    html += `<div class="${color}">${val > 0 ? '+' : ''}${val} ${label}</div>`;
                }
            } else {
                html += `<div class="text-gray-500">Geen module effecten in de buurt</div>`;
            }
            html += `</div>`;

            // Total QOL
            const totalEffects = {
                ...moduleEffects
            };
            for (const type in eventEffects) {
                totalEffects[type] = (totalEffects[type] || 0) + eventEffects[type];
            }
            for (const type in adjacentEventEffects) {
                totalEffects[type] = (totalEffects[type] || 0) + adjacentEventEffects[type];
            }
            const qol = Object.values(totalEffects).reduce((sum, val) => sum + val, 0);

            html += `<div class="mt-1 font-bold ${qol > 0 ? 'text-green-600' : (qol < 0 ? 'text-red-600' : 'text-gray-600')}">
                Totale Kwaliteit van Leven: ${qol > 0 ? '+' : ''}${Math.round(qol * 10) / 10}
            </div>`;

            const overlay = slot.querySelector('.combined-effects');
            if (overlay) {
                overlay.innerHTML = html;
                if (window.applyFontScaleTo && window.currentFontScale) {
                    window.applyFontScaleTo(overlay, window.currentFontScale);
                }
                overlay.classList.remove('hidden');
            }
        });

        document.querySelector('tbody').addEventListener('mouseout', (event) => {
            const slot = event.target.closest('.city-slot');
            if (!slot) return;

            document.querySelectorAll('td.city-cell.bg-green-200').forEach(cell => {
                cell.classList.remove('bg-green-200');
            });

            const overlay = slot.querySelector('.combined-effects');
            if (overlay) overlay.classList.add('hidden');
        });
    });
</script>

<style>
    body {
        transition: background-color 0.5s, color 0.5s;
    }

    .city-cell,
    button,
    .combined-effects {
        transition: background-color 0.5s, color 0.5s;
    }

    /* Dagmodus */
    body.day-mode {
        background-color: #f9fafb;
        /* lichtgrijs of wit */
        color: #1f2937;
        /* donkergrijs */
    }

    /* Alleen Metropolis Grid in nachtmodus zwart maken */
    body.night-mode .city-cell {
        background-color: rgb(50, 64, 90);
    !important;
        /* diep zwart */
        color: #ffffff !important;
        /* witte tekst */
    }

    /* Zorg dat teksten binnen de cell correct wit blijven */
    body.night-mode .city-cell * {
        color: #ffffff !important;
    }

    /* Fix hover overlay (tooltip) in nachtmodus */
    body.night-mode .combined-effects {
        background-color: #1e293b !important;
        /* donkerblauw/grijs */
        border-color: #475569 !important;
        color: #f8fafc !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.8);
    }

    /* Fix de tekstkleuren binnen overlay */
    body.night-mode .combined-effects .font-bold,
    body.night-mode .combined-effects .font-semibold,
    body.night-mode .combined-effects div {
        color: #f8fafc !important;
    }

    /* Nachtmodus: blauwe hoverkleur voor 3x3 buurt */
    body.night-mode .bg-green-200 {
        background-color: rgb(36, 32, 58) !important;
        /* Tailwind 'blue-500' */
        border: 1px solid #93c5fd;
        /* lichtere rand, 'blue-300' */
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }

    }
</style>
