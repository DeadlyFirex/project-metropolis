<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Metropolis Grid</h2>

        <table class="table-auto border-collapse border border-gray-300 w-full text-center">
            <tbody>
            @foreach ($slots->chunk(4) as $row)
                <tr>
                    @foreach ($row as $slot)
                        <td class="border border-gray-300 p-4 w-[200px] h-[150px] bg-gray-100 align-middle text-center city-cell"
                            data-slot-id="{{ $slot->id }}" data-row="{{ $loop->parent->index }}"
                            {{-- rij index --}} data-col="{{ $loop->index }}"> {{-- kolom index --}}

                            <div class="city-slot flex flex-col items-center justify-center h-full relative"
                                 data-slot-id="{{ $slot->id }}"
                                 @if ($slot->module_id) data-module-id="{{ $slot->module_id }}" @endif
                                 {{-- Voeg event data toe als beschikbaar --}}
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

                                        <div
                                            class="grid-effects hidden text-[10px] mt-1 text-gray-600 text-center space-y-[1px]">
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

                                        <div
                                            class="combined-effects hidden absolute top-full mt-2 bg-white border text-[10px] text-gray-800 p-2 rounded shadow z-10">
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

                                {{-- Event effects (hidden by default, used by JS) --}}
                                @if ($slot->event_id != null && $slot->event)
                                    <div class="event-effect-data hidden"> {{-- This div stores the data for JS --}}
                                        @foreach ($slot->event->effects as $effect)
                                            @if ($effect->value !== 0)
                                                <div class="effect-event" data-type="{{ $effect->type }}"
                                                     data-value="{{ $effect->value }}">
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
    document.addEventListener("DOMContentLoaded", () => {
        const cityCells = document.querySelectorAll('td.city-cell');
        const rows = 3; // Pas aan naar jouw data
        const cols = 4; // Pas aan naar jouw data

        function getCell(row, col) {
            const cell = document.querySelector(`.city-cell[data-row="${row}"][data-col="${col}"]`);
            return cell;
        }

        // Voeg een cache toe om API-calls te verminderen
        const eventEffectsCache = {};
        const moduleEffectsCache = {};


        document.querySelector('tbody').addEventListener('mouseover', async (event) => {
            const slot = event.target.closest('.city-slot');
            if (!slot) {
                return;
            }

            const parentTd = slot.closest('td.city-cell');
            if (!parentTd) {
                return;
            }

            const row = parseInt(parentTd.dataset.row, 10);
            const col = parseInt(parentTd.dataset.col, 10);

            const adjacentPositions = [];
            for (let r = row - 1; r <= row + 1; r++) {
                for (let c = col - 1; c <= col + 1; c++) {
                    if (r < 0 || r >= rows || c < 0 || c >= cols) continue;
                    adjacentPositions.push([r, c]);
                }
            }

            const allModuleEffects = {};
            const eventSpecificEffects = {};

            // Reset kleuren van alle cellen en markeer aangrenzende cellen
            document.querySelectorAll('td.city-cell.bg-green-200').forEach(cell => {
                cell.classList.remove('bg-green-200');
            });

            // Verzamel module-effecten via fetch voor elke aangrenzende module
            const modulePromises = [];
            adjacentPositions.forEach(([r, c]) => {
                const cell = getCell(r, c);
                if (!cell) {
                    return;
                }
                cell.classList.add('bg-green-200'); // Markeer als aangrenzend

                const moduleSlot = cell.querySelector('.city-slot[data-module-id]');
                if (moduleSlot) {
                    const moduleId = moduleSlot.dataset.moduleId;
                    if (moduleId && !moduleEffectsCache[moduleId]) {
                        modulePromises.push(
                            fetch(`/api/modules/${moduleId}/effects`)
                                .then(res => res.json())
                                .then(data => {
                                    moduleEffectsCache[moduleId] = data.effects;
                                    return data.effects;
                                })
                                .catch(error => {
                                    console.error(`Error fetching module effects for ID ${moduleId}:`, error);
                                    return [];
                                })
                        );
                    } else if (moduleId) {
                        modulePromises.push(Promise.resolve(moduleEffectsCache[moduleId]));
                    }
                }
            });

            const allFetchedModuleEffects = await Promise.all(modulePromises);
            allFetchedModuleEffects.flat().forEach(effect => {
                // Ensure effect.type and effect.value are correctly accessed
                // Module effects are assumed to be an array of { type: '...', value: X } objects
                if (effect && effect.type && typeof effect.value !== 'undefined') {
                    allModuleEffects[effect.type] = (allModuleEffects[effect.type] || 0) + effect.value;
                }
            });


            // Verzamel evenementeffecten via fetch voor het gehoverde vakje
            const hoveredSlotHasEvent = slot.dataset.eventId;
            let eventName = '';
            let eventImage = '';

            if (hoveredSlotHasEvent) {
                const eventId = slot.dataset.eventId;
                eventName = slot.dataset.eventName || 'Actief Evenement';
                eventImage = slot.dataset.eventImage || '';

                if (eventId && !eventEffectsCache[eventId]) {
                    try {
                        const res = await fetch(`/api/events/${eventId}/effects`);
                        if (!res.ok) {
                            throw new Error(`HTTP error! status: ${res.status}`);
                        }
                        const data = await res.json();
                        eventEffectsCache[eventId] = data.effects; // Cache de resultaten. This will be an object { safety: X, recreation: Y, ...}
                    } catch (error) {
                        console.error(`Error fetching event effects for ID ${eventId}:`, error);
                        eventEffectsCache[eventId] = {}; // Zet leeg object bij fout
                    }
                }

                // Voeg de effecten uit de cache toe aan eventSpecificEffects
                if (eventEffectsCache[eventId]) {
                    // eventEffectsCache[eventId] is now an object, iterate over its properties
                    for (const type in eventEffectsCache[eventId]) {
                        if (Object.prototype.hasOwnProperty.call(eventEffectsCache[eventId], type)) {
                            const value = eventEffectsCache[eventId][type];
                            eventSpecificEffects[type] = (eventSpecificEffects[type] || 0) + value;
                        }
                    }
                }
            }


            const typeMap = {
                safety: 'Veiligheid',
                recreation: 'Recreatie',
                climate: 'Milieukwaliteit',
                facilities: 'Voorzieningen',
                infrastructure: 'Mobiliteit'
            };

            let html = '';

            // Toon Evenementdetails en effecten indien aanwezig
            if (hoveredSlotHasEvent) {
                html += `<div class="mb-2 pb-2 border-b border-gray-300">`;
                html += `<div class="font-bold text-gray-800 text-sm mb-1">Actief Evenement: ${eventName}</div>`;
                if (eventImage) {
                    html += `<img src="${eventImage}" alt="${eventName}" class="w-10 h-10 object-contain mx-auto mb-1">`;
                }
                if (Object.keys(eventSpecificEffects).length > 0) {
                    for (const type in eventSpecificEffects) {
                        const val = eventSpecificEffects[type];
                        const label = typeMap[type] || type;
                        const color = val > 0 ? 'text-green-600' : (val < 0 ? 'text-red-600' : 'text-gray-600');
                        html += `<div class="${color}">${val > 0 ? '+' : ''}${val} ${label}</div>`;
                    }
                } else {
                    html += `<div class="text-gray-500">Geen specifieke evenementeffecten</div>`;
                }
                html += `</div>`;
            }

            // Toon Gecombineerde Module-effecten
            html += `<div class="mb-2 pb-2 border-b border-gray-300">`;
            html += `<div class="font-bold text-gray-800 text-sm mb-1">Gezamenlijke Module Effecten (buurt):</div>`;
            if (Object.keys(allModuleEffects).length > 0) {
                for (const type in allModuleEffects) {
                    const val = allModuleEffects[type];
                    const label = typeMap[type] || type;
                    const color = val > 0 ? 'text-green-600' : (val < 0 ? 'text-red-600' : 'text-gray-600');
                    html += `<div class="${color}">${val > 0 ? '+' : ''}${val} ${label}</div>`;
                }
            } else {
                html += `<div class="text-gray-500">Geen module effecten in de buurt</div>`;
            }
            html += `</div>`;

            // Bereken en toon de Totale Kwaliteit van Leven (evenement + modules)
            const totalEffects = { ...allModuleEffects
            }; // Begin met module-effecten
            for (const type in eventSpecificEffects) { // Voeg evenementeffecten toe
                totalEffects[type] = (totalEffects[type] || 0) + eventSpecificEffects[type];
            }
            const qol = Object.values(totalEffects).reduce((sum, val) => sum + val, 0);

            html += `<div class="mt-1 font-bold ${qol > 0 ? 'text-green-600' : (qol < 0 ? 'text-red-600' : 'text-gray-600')}">
            Totale Kwaliteit van Leven: ${qol > 0 ? '+' : ''}${qol}
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
