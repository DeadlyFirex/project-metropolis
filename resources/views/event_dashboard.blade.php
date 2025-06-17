<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Events Dashboard</h2>
            <button class="bg-blue-500 text-white px-4 py-2 text-sm sm:text-base rounded" id="increaseFontBtn">
                Tekstgrootte Vergroten
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

                {{-- Geplande & Actieve events --}}
                @if(!empty($activeEvents))
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                        <h4 class="font-semibold mb-2">Geplande &amp; Actieve Events:</h4>

                        @foreach($activeEvents as $slotId => $event)
                            @if(!str_contains($event['event_name'], '(Aangrenzend)'))
                                @php($running = ($event['status'] ?? 'running') === 'running')
                                <div class="flex items-center justify-between mb-2 last:mb-0">
                                    <div>
                                        <strong>Vakje {{ $slotId }}:</strong> {{ $event['event_name'] }}

                                        <span class="ml-2 text-sm">
                            {{ $running ? 'Nog' : 'Start over' }} {{ $event['time_remaining'] }}
                        </span>

                                        @if($event['is_recurring'])
                                            <span class="ml-2 bg-blue-200 text-blue-800 px-2 py-1 rounded text-xs">Terugkerend</span>
                                        @endif

                                        @if(!$running)
                                            <span class="ml-2 bg-gray-200 text-gray-800 px-2 py-1 rounded text-xs">Gepland</span>
                                        @endif
                                    </div>

                                    <form action="{{ route('events.reset') }}?time={{ request()->query('time') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="slot_id" value="{{ $slotId }}">
                                        <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                                            Verwijder
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif


            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Selecteer Event voor Specifiek Vakje</h3>

                    <form action="{{ route('events.set') }}?time={{ request()->query('time') }}" method="POST" id="eventForm">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="event_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Naam <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="event_name" id="event_name" value="{{ old('event_name') }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                                       placeholder="Voer event naam in">

                                <label for="event_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 mt-4">
                                    Omschrijving <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="event_description" id="event_description" value="{{ old('event_description') }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                                       placeholder="Voer event omschrijving in">

                                <label for="slot_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 mt-4">
                                    Selecteer Vakje <span class="text-red-500">*</span>
                                </label>
                                <select name="slot_id" id="slot_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required>
                                    <option value="">Kies een vakje...</option>
                                    @foreach($slots as $slot)
                                        <option value="{{ $slot->id }}" data-module="{{ $slot->module_id }}">
                                            Vakje {{ $slot->id }}
                                            @if($slot->module)
                                                ({{ $slot->module->name }})
                                            @else
                                                (Leeg)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('slot_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="event_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Event Type <span class="text-red-500">*</span>
                                </label>
                                <select name="event_type" id="event_type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required>
                                    <option value="">Selecteer een event...</option>
                                    @foreach($event_types as $id => $description)
                                        <option value="{{ $id }}">
                                            {{ $id }}
                                            @if(strtolower($id) === 'festival' || strtolower($id) === 'concert')
                                                (voor park)
                                            @elseif(strtolower($id) === 'markt')
                                                (voor weg)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('event_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Tijdsvenster en Recurring --}}
                        <div class="mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-4">Tijdsvenster</h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Begintijd <span class="text-red-500">*</span>
                                    </label>
                                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-gray-100">
                                    @error('start_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Eindtijd <span class="text-red-500">*</span>
                                    </label>
                                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-gray-100">
                                    @error('end_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-4 flex items-center">
                                <input type="checkbox" name="is_recurring" id="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_recurring" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Terugkerend (dagelijks)
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                                Event Instellen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>[x-cloak]{display:none!important;}</style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const eventTypeSelect = document.getElementById('event_type');
            const slotSelect      = document.getElementById('slot_id');
            const eventTypeModules = @json($event_type_modules);

            // zet initieel uit
            slotSelect.disabled = true;

            eventTypeSelect.addEventListener('change', function() {
                const selectedType = this.value;
                slotSelect.disabled = !selectedType;

                // Show only compatible slots
                Array.from(slotSelect.options).forEach(option => {
                    if (!option.dataset.module) return; // Skip placeholder
                    const slotModule = option.getAttribute('data-module');
                    const compatibleModule = eventTypeModules[selectedType];
                    option.style.display = (slotModule == compatibleModule) ? '' : 'none';
                });

                // Reset selection if current is not compatible
                if (slotSelect.selectedIndex > 0 && slotSelect.options[slotSelect.selectedIndex].style.display === 'none') {
                    slotSelect.selectedIndex = 0;
                }
            });
        });
    </script>
</x-app-layout>
