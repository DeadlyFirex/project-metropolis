<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="mb-6 flex items-center justify-between">
            <div class="text-lg font-medium text-gray-800 dark:text-gray-200">
                Digitale Klok: <span id="clock">{{ $clockTime ?: '00:00:00' }}</span>
            </div>
            <button onclick="toggleDayNight()" class="bg-blue-500 text-white px-4 py-1 rounded shadow text-sm">
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
                                    @if ($slot->module_id) data-module-id="{{ $slot->module_id }}" @endif>

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

                                            <!-- Overlay voor gecombineerde effecten -->
                                            <div
                                                class="combined-effects hidden absolute top-full mt-2 bg-white border text-[10px] text-gray-800 p-2 rounded shadow z-10">
                                            </div>

                                            <!-- Verwijderknop -->
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
    // Start met de kloktijd of fallback
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

    // Elke seconde klok updaten
    setInterval(() => {
        tickClock();
        maybeSaveTime();
    }, 1000);

    // Timer voor saven
    let lastSave = 0;

    function maybeSaveTime() {
        if (Date.now() - lastSave >= 15000) { // elke 15 sec
            saveTime();
            lastSave = Date.now();
        }
    }

    // CSRF token (uit meta tag)
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Save time via fetch POST
    function saveTime() {
        fetch('/save-clock', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin', // zorgt dat cookies meegestuurd worden
            body: JSON.stringify({
                time: currentTime
            })
        }).catch(e => {
            // eventueel error handling/loggen
            console.error('Tijd opslaan mislukt', e);
        });
    }

    // Dag/Nacht toggle knop
    function toggleDayNight() {
        const hour = parseInt(currentTime.split(':')[0]);
        // Als tijd tussen 6 en 18 (dag), zet op 00:00:00 (nacht)
        // anders op 12:00:00 (dag)
        if (hour >= 6 && hour < 18) {
            currentTime = '00:00:00';
        } else {
            currentTime = '12:00:00';
        }
        document.getElementById('clock').innerText = currentTime;
        saveTime();
    }

    // Save tijd ook bij verlaten pagina
    window.addEventListener('beforeunload', function(event) {
        // Gebruik navigator.sendBeacon voor betrouwbare versturing
        const url = '/save-clock';
        const data = new FormData();
        data.append('time', currentTime);
        data.append('_token', csrfToken);

        navigator.sendBeacon(url, data);
    });

    // Rest van je city grid hover effect code blijft ongewijzigd
    const cityCells = document.querySelectorAll('td.city-cell');
    const rows = 3;
    const cols = 4;

    function getCell(row, col) {
        return document.querySelector(`.city-cell[data-row="${row}"][data-col="${col}"]`);
    }

    document.querySelector('tbody').addEventListener('mouseover', (event) => {
        const slot = event.target.closest('.city-slot');
        if (!slot || !slot.dataset.moduleId) return;

        const parentTd = slot.closest('td.city-cell');
        if (!parentTd) return;

        const row = parseInt(parentTd.dataset.row, 10);
        const col = parseInt(parentTd.dataset.col, 10);

        const adjacentPositions = [];
        for (let r = row - 1; r <= row + 1; r++) {
            for (let c = col - 1; c <= col + 1; c++) {
                if (r < 0 || r >= rows || c < 0 || c >= cols) continue;
                adjacentPositions.push([r, c]);
            }
        }

        const allEffects = {};

        adjacentPositions.forEach(([r, c]) => {
            const cell = getCell(r, c);
            if (!cell) return;

            cell.classList.add('bg-green-200');

            const moduleSlot = cell.querySelector('.city-slot[data-module-id]');
            if (!moduleSlot) return;

            const effects = cell.querySelectorAll('.effect');
            effects.forEach(effect => {
                const type = effect.dataset.type;
                const value = parseInt(effect.dataset.value, 10);
                allEffects[type] = (allEffects[type] || 0) + value;
            });
        });

        const qol = Object.values(allEffects).reduce((sum, val) => sum + val, 0);

        const typeMap = {
            safety: 'Veiligheid',
            recreation: 'Recreatie',
            climate: 'Milieukwaliteit',
            facilities: 'Voorzieningen',
            infrastructure: 'Mobiliteit'
        };

        let html = '';
        for (const type in allEffects) {
            const val = allEffects[type];
            const label = typeMap[type] || type;
            const color = val > 0 ? 'text-green-600' : (val < 0 ? 'text-red-600' : 'text-gray-600');
            html += `<div class="${color}">${val > 0 ? '+' : ''}${val} ${label}</div>`;
        }
        html += `<div class="mt-1 font-bold ${qol > 0 ? 'text-green-600' : (qol < 0 ? 'text-red-600' : 'text-gray-600')}">
            Kwaliteit van Leven: ${qol > 0 ? '+' : ''}${qol}
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
</script>
