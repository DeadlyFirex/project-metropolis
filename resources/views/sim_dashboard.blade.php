<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Simulatie Dashboard') }}
            </h2>
            <div class="flex gap-2">
                <button class="bg-blue-500 text-white px-4 py-2 text-sm sm:text-base rounded" id="increaseFontBtn">
                    Tekstgrootte Vergroten
                </button>
                <button onclick="copyEventsToPrintVersion(); window.print()" class="bg-green-600 text-white px-4 py-2 text-sm sm:text-base rounded">
                    Download als PDF
                </button>
            </div>
        </div>
    </x-slot>

    <div class="w-full bg-gray-100 min-h-screen">
        <div class="flex flex-col gap-6 px-4 sm:px-6 pt-6">

            {{-- Rij 1: City Grid (links) + rechterkolom met Library en Effects --}}
            <div class="flex flex-col lg:flex-row gap-6 items-start">

                {{-- City Grid links --}}
                <main class="w-full max-w-full lg:max-w-[1000px] bg-white p-4 sm:p-6 rounded-2xl shadow">
                    @include('components.city-grid', ['slots' => $slots, 'clockTime' => $clockTime])
                </main>

                {{-- Rechterkant: Bibliotheek + Calculated Effects --}}
                <div class="w-full lg:flex-1 flex flex-col gap-6">
                    {{-- Module Bibliotheek (boven) --}}
                    <section
                        class="bg-white dark:bg-gray-900 px-4 py-6 rounded-2xl shadow w-full h-[400px] overflow-y-auto">
                        @include('components.library', [
                        'modules' => $modules,
                        'categories' => $categories,
                        ])
                    </section>

                    {{-- Calculated Effects (zichtbaar) --}}
                    <section id="effect-view"
                        class="bg-white dark:bg-gray-900 px-4 py-6 rounded-2xl shadow w-full overflow-x-auto">
                        @include('components.calculated-effects', ['slots' => $slots])
                    </section>

                    {{-- Effect Control (verborgen) --}}
                    <section id="effect-control-view"
                        class="hidden bg-white dark:bg-gray-900 px-4 py-6 rounded-2xl shadow w-full overflow-x-auto">
                        @include('components.effect-control', ['modules' => $modules])
                    </section>
                </div>
            </div>

            {{-- Active Events Section --}}
            <div class="w-full" id="activeEventsSection">
                <div class="bg-white dark:bg-gray-900 px-6 py-4 rounded-2xl shadow">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Actieve Events</h3>
                    <div id="activeEventsList">
                        <p class="text-gray-500 dark:text-gray-400" id="noEventsMessage">Geen actieve events</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Printbare versie van het rapport, wordt alleen zichtbaar tijdens afdrukken (window.print) --}}
    <div id="printable-area" class="hidden print:block absolute left-[-9999px] top-0 w-full">
        <div class="p-6 bg-white text-black w-full">

            {{-- Header met datum en tijd --}}
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <h1 class="text-2xl font-bold">Simulatie Rapport</h1>
                <div class="text-right text-gray-600">
                    <div class="text-sm">{{ config('app.name', 'Laravel') }}</div>
                </div>
            </div>

            {{-- Toon de Metropolis Grid zoals die nu staat --}}
            <div class="mb-8">
                @include('components.city-grid', ['slots' => $slots])
            </div>

            {{-- Tabel met berekende effecten per module --}}
            <div class="mb-8 overflow-visible w-full">
                <h2 class="text-xl font-semibold mb-4">Effecten op de Grid</h2>

                {{-- Effectentabel met alle modules en hun waarden --}}
                <div style="overflow: visible !important;">
                    <table class="w-full text-xs text-left border-collapse border border-gray-300">

                        {{-- Tabelkoppen: effectcategorieën --}}
                        <thead class="bg-gray-100 text-gray-800">
                            <tr>
                                <th class="px-2 py-1 border border-gray-300">Module</th>
                                <th class="px-2 py-1 border border-gray-300">Veiligheid</th>
                                <th class="px-2 py-1 border border-gray-300">Recreatie</th>
                                <th class="px-2 py-1 border border-gray-300">Milieukwaliteit</th>
                                <th class="px-2 py-1 border border-gray-300">Voorzieningen</th>
                                <th class="px-2 py-1 border border-gray-300">Mobiliteit</th>
                                <th class="px-2 py-1 border border-gray-300">Kwaliteit van Leven</th>
                            </tr>
                        </thead>

                        {{-- Inhoud van de tabel: per module de effectwaarden ophalen --}}
                        <tbody class="bg-white">
                            @php
                            // Initialiseer totaalwaarden voor elk effecttype
                            $totals = ['safety' => 0, 'recreation' => 0, 'climate' => 0, 'facilities' => 0, 'infrastructure' => 0];
                            @endphp

                            @foreach ($slots as $slot)
                            @if($slot->module)
                            @php
                            // Verzamel de waarden van elk effecttype voor de huidige module
                            $effects = [
                            'safety' => $slot->module->effects->firstWhere('type', 'safety')?->value ?? 0,
                            'recreation' => $slot->module->effects->firstWhere('type', 'recreation')?->value ?? 0,
                            'climate' => $slot->module->effects->firstWhere('type', 'climate')?->value ?? 0,
                            'facilities' => $slot->module->effects->firstWhere('type', 'facilities')?->value ?? 0,
                            'infrastructure' => $slot->module->effects->firstWhere('type', 'infrastructure')?->value ?? 0,
                            ];

                            // Tel deze op bij de totalen
                            foreach ($effects as $type => $val) {
                            $totals[$type] += $val;
                            }

                            // Totale kwaliteit van leven berekenen als som van alle effecten
                            $qol = array_sum($effects);
                            @endphp

                            {{-- Rijtje voor deze module --}}
                            <tr>
                                <td class="border px-2 py-1">{{ $slot->module->name }}</td>
                                <td class="border px-2 py-1">{{ $effects['safety'] }}</td>
                                <td class="border px-2 py-1">{{ $effects['recreation'] }}</td>
                                <td class="border px-2 py-1">{{ $effects['climate'] }}</td>
                                <td class="border px-2 py-1">{{ $effects['facilities'] }}</td>
                                <td class="border px-2 py-1">{{ $effects['infrastructure'] }}</td>
                                <td class="border px-2 py-1 font-semibold">{{ $qol }}</td>
                            </tr>
                            @endif
                            @endforeach

                            {{-- Rij voor Eventeffecten --}}
                            @php
                            $eventEffects = ['safety' => 0, 'recreation' => 0, 'climate' => 0, 'facilities' => 0, 'infrastructure' => 0];
                            // voorbeelddata, vervang dit als je echte event-effecten beschikbaar hebt
                            @endphp
                            <tr class="bg-blue-100 font-semibold">
                                <td class="border px-2 py-1">Evenementen Overzicht</td>
                                <td class="border px-2 py-1">{{ $eventEffects['safety'] > 0 ? '+' : '' }}{{ $eventEffects['safety'] }}</td>
                                <td class="border px-2 py-1">{{ $eventEffects['recreation'] > 0 ? '+' : '' }}{{ $eventEffects['recreation'] }}</td>
                                <td class="border px-2 py-1">{{ $eventEffects['climate'] > 0 ? '+' : '' }}{{ $eventEffects['climate'] }}</td>
                                <td class="border px-2 py-1">{{ $eventEffects['facilities'] > 0 ? '+' : '' }}{{ $eventEffects['facilities'] }}</td>
                                <td class="border px-2 py-1">{{ $eventEffects['infrastructure'] > 0 ? '+' : '' }}{{ $eventEffects['infrastructure'] }}</td>
                                <td class="border px-2 py-1 font-semibold">
                                    {{ array_sum($eventEffects) > 0 ? '+' : '' }}{{ array_sum($eventEffects) }}
                                </td>
                            </tr>


                        </tbody>

                        {{-- Voetrij met totaalsom van alle effecten over alle modules --}}
                        <tfoot>
                            @php
                            $totalQol = array_sum($totals);
                            @endphp
                            <tr class="bg-gray-200 font-bold">
                                <td class="border px-2 py-1">Totaal</td>
                                <td class="border px-2 py-1">{{ $totals['safety'] }}</td>
                                <td class="border px-2 py-1">{{ $totals['recreation'] }}</td>
                                <td class="border px-2 py-1">{{ $totals['climate'] }}</td>
                                <td class="border px-2 py-1">{{ $totals['facilities'] }}</td>
                                <td class="border px-2 py-1">{{ $totals['infrastructure'] }}</td>
                                <td class="border px-2 py-1">{{ $totalQol }}</td>
                            </tr>
                        </tfoot>

                    </table>
                </div>
            </div>

            {{-- Actieve Events sectie voor PDF --}}
            <div class="w-full">
                <h2 class="text-xl font-semibold mb-4">Actieve Events</h2>

                <div id="printableEventsList">
                    {{-- Dit wordt gevuld door JavaScript voordat er geprint wordt --}}
                    <p class="text-gray-600">Laden van events...</p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="border-t pt-4 mt-8 text-center text-gray-500 text-sm">
                <p>Gegenereerd op {{ date('d-m-Y om H:i:s') }} | {{ config('app.name', 'Simulatie Platform') }}</p>
            </div>

        </div>
    </div>

    <script>
        function copyEventsToPrintVersion() {
            const dashboardEvents = document.getElementById('activeEventsList').innerHTML;
            const printableEvents = document.getElementById('printableEventsList');
            if (printableEvents && dashboardEvents) {
                printableEvents.innerHTML = dashboardEvents;
            }
        }
        // Function to fetch and display active events
        function updateActiveEvents() {
            fetch('/events/slot-events')
                .then(response => response.json())
                .then(data => {
                    const eventsList = document.getElementById('activeEventsList');
                    const noEventsMessage = document.getElementById('noEventsMessage');

                    if (Object.keys(data).length === 0) {
                        eventsList.innerHTML = '<p class="text-gray-500 dark:text-gray-400" id="noEventsMessage">Geen actieve events</p>';
                    } else {
                        let eventsHtml = '';
                        for (const [slotId, event] of Object.entries(data)) {
                            if (event.event_name && event.event_name.includes('(Aangrenzend)')) {
                                continue; // Skip adjacent events
                            }
                            eventsHtml += `
                                <div class="flex items-center justify-between p-3 mb-2 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg">
                                    <div class="flex items-center space-x-4">
                                        <div class="bg-yellow-500 text-white px-2 py-1 rounded text-sm font-medium">
                                            Vakje ${slotId}
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-800 dark:text-gray-200">${event.event_name}</span>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                Nog ${event.time_remaining} resterend
                                                ${event.is_recurring ? '<span class="ml-2 bg-blue-200 text-blue-800 px-2 py-1 rounded text-xs">Terugkerend</span>' : ''}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse" title="Actief"></div>
                                        <a href="/events" class="text-blue-600 hover:text-blue-800 text-sm">
                                            Beheren
                                        </a>
                                    </div>
                                </div>
                            `;
                        }
                        eventsList.innerHTML = eventsHtml;
                    }
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                });
        }

        // Update events on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateActiveEvents();
            // Update events every 30 seconds
            setInterval(updateActiveEvents, 30000);
        });

        // Add visual indicators to grid cells with active events
        function highlightActiveEventSlots(activeEvents) {
            // Remove existing highlights
            document.querySelectorAll('.slot-with-event').forEach(slot => {
                slot.classList.remove('slot-with-event');
            });
            // Add highlights for active events
            for (const slotId in activeEvents) {
                const slotElement = document.querySelector(`[data-slot-id="${slotId}"]`);
                if (slotElement) {
                    slotElement.classList.add('slot-with-event');
                }
            }
        }
    </script>
    <style>
        /* Add visual indicator for slots with active events */
        .slot-with-event {
            position: relative;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.5) !important;
        }

        .slot-with-event::after {
            content: '⚡';
            position: absolute;
            top: -8px;
            right: -8px;
            background: #f59e0b;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            z-index: 10;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</x-app-layout>