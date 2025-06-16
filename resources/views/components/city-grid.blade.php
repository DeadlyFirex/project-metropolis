<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <div class="text-lg font-medium text-gray-800 dark:text-gray-200">
                <span id="clock">{{ $clockTime ?: '00:00:00' }}</span>
        <button class="speed-button bg-blue-500 text-white px-2 py-1 text-sm sm:text-base rounded" data-speed="0.5">0.5X</button>
        <button class="speed-button bg-blue-500 text-white px-2 py-1 text-sm sm:text-base rounded" data-speed="1">1X</button>
        <button class="speed-button bg-blue-500 text-white px-2 py-1 text-sm sm:text-base rounded" data-speed="2">2X</button>
        <button class="speed-button bg-blue-500 text-white px-2 py-1 text-sm sm:text-base rounded" data-speed="5">5X</button>
        <button class="speed-button bg-blue-500 text-white px-2 py-1 text-sm sm:text-base rounded" data-speed="10">10X</button>

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
    let interval = null;
    let currentSpeed = 1;

    function pad(num) {
        return String(num).padStart(2, '0');
    }

    function tickClock() {
        let [h, m, s] = currentTime.split(':').map(Number);
        s += currentSpeed;
        if (s >= 60) {
            m += Math.floor(s / 60);
            s = s % 60;
        }
        if (m >= 60) {
            h += Math.floor(m / 60);
            m = m % 60;
        }
        if (h >= 24) {
            h = h % 24;
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

    function maybeSaveTime() {
        if (Date.now() - lastSave >= 15000) {
            saveTime();
            lastSave = Date.now();
        }
    }

    function startClockInterval() {
        if (interval) clearInterval(interval);
        interval = setInterval(() => {
            tickClock();
            checkAndApplyNightMode();
            maybeSaveTime();
        }, 1000 / currentSpeed); // Adjust speed
    }

    // Attach event listeners to speed buttons
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.speed-button').forEach(button => {
            button.addEventListener('click', () => {
                currentSpeed = parseFloat(button.dataset.speed);
                startClockInterval();
            });
        });

        checkAndApplyNightMode(); // Initial night mode check
        startClockInterval();     // Start the clock ticking
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let lastSave = 0;

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

    window.addEventListener('beforeunload', function () {
        const url = '/save-clock';
        const data = new FormData();
        data.append('time', currentTime);
        data.append('_token', csrfToken);
        navigator.sendBeacon(url, data);
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