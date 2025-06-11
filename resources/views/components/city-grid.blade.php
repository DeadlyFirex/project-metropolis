<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <div class="text-lg font-medium text-gray-800 dark:text-gray-200">
                <span id="clock">{{ $clockTime ?: '00:00:00' }}</span>
            </div>
            <button onclick="toggleDayNight()" class="bg-blue-500 text-white px-4 py-1 rounded shadow text-sm pdf-hide">
                Dag/Nacht
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
                                     @if ($slot->event_id)
                                         data-event-id="{{ $slot->event_id }}"
                                     data-event-name="{{ $slot->event->name ?? 'Actief Evenement' }}"
                                     data-event-image="{{ $slot->event->image_path ? asset('storage/' . $slot->event->image_path) : '' }}"
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
    let currentTime = '{{ $clockTime ?: '00:00:00' }}';

    function pad(num) {
        return String(num).padStart(2, '0');
    }

    function tickClock() {
        let [h, m, s] = currentTime.split(':').map(Number);
        s++;
        if (s >= 60) {
            s = 0;
            m++;
        }
        if (m >= 60) {
            m = 0;
            h++;
        }
        if (h >= 24) {
            h = 0;
        }
        currentTime = `${pad(h)}:${pad(m)}:${pad(s)}`;
        document.getElementById('clock').innerText = currentTime;
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
        saveTime();
    }

    window.addEventListener('beforeunload', function() {
        const url = '/save-clock';
        const data = new FormData();
        data.append('time', currentTime);
        data.append('_token', csrfToken);
        navigator.sendBeacon(url, data);
    });

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
            const totalEffects = {...moduleEffects};
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

    /* Nachtmodus */
    body.night-mode {
        background-color: #1a202c;
        /* donkerblauw/grijs */
        color: #f7fafc;
        /* lichte tekst (witachtig) */
    }

    /* Zet standaard tekst in nacht wit */
    body.night-mode,
    body.night-mode * {
        color: #f7fafc !important;
    }

    /* Witte achtergrond-blokken behouden zwarte tekst */
    body.night-mode .bg-white,
    body.night-mode .bg-gray-50,
    body.night-mode .bg-[#ffffff] {
        color: #2d3748 !important;
        /* zwart */
    }

    /* Tekst in elementen binnen witte blokken ook zwart */
    body.night-mode .bg-white *,
    body.night-mode .bg-gray-50 * {
        color: #000000 !important;
    }

    /* Grid cell achtergrond in nachtmodus donker maken */
    body.night-mode .city-cell {
        background-color: #2d3748 !important;
    }

    /* Effect beschrijvingen in nacht */
    body.night-mode .effect {
        color: #f7fafc !important;
        /* lichte tekst */
    }

    /* Donkerder achtergrond voor gecombineerde effecten */
    body.night-mode .combined-effects {
        background-color: #2d3748;
        border-color: #4a5568;
        color: #f7fafc;
    }

    /* Knoppen nachtmodus */
    body.night-mode button {
        background-color: #4a5568;
        color: #f7fafc;
    }

    /* Categorie filter nachtmodus */
    body.night-mode .category {
        background-color: #2d3748;
        /* Donkere achtergrond */
        color: #f7fafc;
        /* lichte tekst */
    }

    body.night-mode .category select,
    body.night-mode .category option,
    body.night-mode .category label {
        background-color: #2d3748;
        color: #f7fafc;
    }

    /* Donkergroen highlight aanpassen voor nacht */
    body.night-mode .bg-green-200 {
        background-color: #22543d !important;
    }
</style>