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

            @if(session('active_event'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <strong>Actief Event:</strong> {{ $events[session('active_event.type')] ?? session('active_event.type') }}
                            @if(session('active_event.duration'))
                                <span class="ml-2">Duur: {{ session('active_event.duration') }} {{ session('active_event.duration_unit') }}</span>
                            @endif
                            @if(session('active_event.is_recurring'))
                                <span class="ml-2 bg-blue-200 text-blue-800 px-2 py-1 rounded text-xs">Terugkerend</span>
                            @endif
                        </div>
                        <form action="{{ route('events.reset') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                                Terug naar Normaal
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Selecteer Event Omstandigheden</h3>

                    <form action="{{ route('events.set') }}" method="POST" id="eventForm">
                        @csrf

                        <div class="mb-6">
                            <label for="event_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Event Type
                            </label>
                            <select name="event_type" id="event_type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required>
                                <option value="">Selecteer een event...</option>
                                @foreach($events as $key => $name)
                                    <option value="{{ $key }}" {{ old('event_type') == $key ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('event_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="durationSettings" class="mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600" style="display: none;">
                            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-4">Duur Instellingen</h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Duur
                                    </label>
                                    <input type="number" name="duration" id="duration" min="1" value="{{ old('duration') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-gray-100"
                                           placeholder="Voer duur in">
                                </div>

                                <div>
                                    <label for="duration_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Eenheid
                                    </label>
                                    <select name="duration_unit" id="duration_unit" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-gray-100">
                                        <option value="minutes" {{ old('duration_unit') == 'minutes' ? 'selected' : '' }}>Minuten</option>
                                        <option value="hours" {{ old('duration_unit') == 'hours' ? 'selected' : '' }}>Uren</option>
                                        <option value="days" {{ old('duration_unit') == 'days' ? 'selected' : '' }}>Dagen</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="flex items-center mb-4">
                                    <input type="checkbox" name="is_recurring" id="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="is_recurring" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                        Maak dit event terugkerend
                                    </label>
                                </div>

                                <div id="recurringSettings" class="grid grid-cols-1 md:grid-cols-2 gap-4" style="display: none;">
                                    <div>
                                        <label for="recurring_interval" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Herhaal elke
                                        </label>
                                        <input type="number" name="recurring_interval" id="recurring_interval" min="1" value="{{ old('recurring_interval') }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-gray-100"
                                               placeholder="Interval">
                                    </div>

                                    <div>
                                        <label for="recurring_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Eenheid
                                        </label>
                                        <select name="recurring_unit" id="recurring_unit" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-600 dark:border-gray-500 dark:text-gray-100">
                                            <option value="minutes" {{ old('recurring_unit') == 'minutes' ? 'selected' : '' }}>Minuten</option>
                                            <option value="hours" {{ old('recurring_unit') == 'hours' ? 'selected' : '' }}>Uren</option>
                                            <option value="days" {{ old('recurring_unit') == 'days' ? 'selected' : '' }}>Dagen</option>
                                        </select>
                                    </div>
                                </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            const eventSelect = document.getElementById('event_type');
            const durationSettings = document.getElementById('durationSettings');
            const isRecurringCheckbox = document.getElementById('is_recurring');
            const recurringSettings = document.getElementById('recurringSettings');

            // Show/hide duration settings based on event selection
            eventSelect.addEventListener('change', function() {
                if (this.value) { // Show if any event type is selected
                    durationSettings.style.display = 'block';
                } else {
                    durationSettings.style.display = 'none';
                }
            });

            // Show/hide recurring settings based on checkbox
            isRecurringCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    recurringSettings.style.display = 'grid';
                } else {
                    recurringSettings.style.display = 'none';
                }
            });

            // Initialize on page load
            if (eventSelect.value) { // Show if any event type is pre-selected
                durationSettings.style.display = 'block';
            }

            if (isRecurringCheckbox.checked) {
                recurringSettings.style.display = 'grid';
            }
        });
    </script>
</x-app-layout>
