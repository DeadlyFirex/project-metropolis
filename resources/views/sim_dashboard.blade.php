<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Simulatie Dashboard') }}
            </h2>
            <button class="bg-blue-500 text-white px-4 py-2 text-sm sm:text-base rounded" id="increaseFontBtn">
                Tekstgrootte Vergroten
            </button>
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

    <script>
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
        [x-cloak]{display:none!important;}
    </style>
        </div>
    </div>
</x-app-layout>
