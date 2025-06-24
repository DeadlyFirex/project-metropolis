@php use Carbon\Carbon; @endphp
<meta name="slot-events-url" content="{{ route('events.slot-events') }}">
<meta name="clock-save-url" content="{{ route('clock.save') }}">
<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-6 mb-6">

            <!-- Clock & Date Display -->
            <div class="flex flex-col items-start sm:items-start text-left">
                <div class="text-4xl font-semibold text-gray-800 tracking-wider" id="clock" data-start="{{ $clockTime ?: '00:00:00' }}">
                    00:00:00
                </div>
                <div class="text-lg text-gray-600  mt-1" id="date" data-start="{{ $clockDate ?: '1974-01-01' }}">
                    1974-01-01
                </div>
            </div>

            <!-- Button Dashboard -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-md px-4 py-3 flex flex-wrap gap-2 justify-center sm:justify-start max-w-full">
                <!-- Play/Pause -->
                <button onclick="resumeClock()"
                        title="Hervat simulatie"
                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 text-sm rounded-md shadow-sm">
                    ▶ Hervat
                </button>
                <button onclick="pauseClock()"
                        title="Pauzeer simulatie"
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 text-sm rounded-md shadow-sm">
                    ⏸ Pauzeer
                </button>

                <!-- Speed Controls -->
                <button onclick="accelerateClock(1)"
                        title="1x snelheid"
                        class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1.5 text-sm rounded-md shadow-sm">
                    1x
                </button>
                <button onclick="accelerateClock(25)"
                        title="25x versnellen"
                        class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1.5 text-sm rounded-md shadow-sm">
                    25x
                </button>
                <button onclick="accelerateClock(1000)"
                        title="∞ versnelling"
                        class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1.5 text-sm rounded-md shadow-sm">
                    ∞
                </button>

                <!-- Time Skip -->
                <button onclick="skipMinutes(1)"
                        title="Skip 1 minuut"
                        class="bg-blue-400 hover:bg-blue-500 text-white px-3 py-1.5 text-sm rounded-md shadow-sm">
                    +1 min
                </button>
                <button onclick="skipHours(1)"
                        title="Skip 1 uur"
                        class="bg-blue-400 hover:bg-blue-500 text-white px-3 py-1.5 text-sm rounded-md shadow-sm">
                    +1 uur
                </button>

                <span id="mode-icon" class="text-2xl">?</span>
            </div>
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
                                    data-slot-id="{{ $slot->id }}" data-approved="{{ $slot->approved ? '1' : '0' }}"
                                    @if ($slot->module_id) data-module-id="{{ $slot->module_id }}" @endif
                                    @if ($slot->event_id) data-event-id="{{ $slot->event_id }}"
                            data-event-name="{{ $slot->event->name ?? 'Actief Evenement' }}"
                            data-event-image="{{ $slot->event->image_path ? asset('storage/' . $slot->event->image_path) : '' }}" @endif>
                                    @if ($slot->approved)
                                        <span class="absolute top-1 right-1 text-green-600 text-xs" title="Goedgekeurd">
                                            🔒
                                        </span>
                                    @endif
                                    @if ($slot->module_id != null && $slot->module && $slot->module->image_path)
                                        <div class="slot-module relative flex flex-col items-center" draggable="true"
                                            data-module-id="{{ $slot->module_id }}">

                                            @if (!$slot->approved)
                                                <form method="POST" action="{{ route('slots.approve', $slot->id) }}"
                                                    class="absolute top-0 left-0" onsubmit="showLoading()">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="bg-green-500 text-white rounded-full w-5 h-5 text-xs leading-none"
                                                        title="Goedkeuren (vergrendelen)">
                                                        ✓
                                                    </button>
                                                </form>
                                            @endif

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
                                            @if (!$slot->approved)
                                                <form method="POST"
                                                    action="{{ route('slots.removeModule', $slot->id) }}"
                                                    class="absolute top-0 right-0" onsubmit="showLoading()">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="bg-red-500 text-white rounded-full w-5 h-5 text-xs leading-none">
                                                        ×
                                                    </button>
                                                </form>
                                            @endif
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
                                                    <div class="effect-event" data-type="{{ $effect->type }}"
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
<div id="loading"
    class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex justify-center items-center min-h-screen">
    <div class="w-10 h-10 border-4 border-gray-200 border-t-blue-500 rounded-full animate-spin"></div>
</div>

<script>
    
    
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
</style>