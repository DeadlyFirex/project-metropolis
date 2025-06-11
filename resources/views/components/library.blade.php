<div class="py-2 px-2 h-full flex flex-col bg-white dark:bg-gray-900 rounded-2xl">

    {{-- Titel en bovenste knoppen/filter --}}
    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2 text-left">Module Bibliotheek</h2>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
        <button onclick="window.location.href='{{ route("module.index") }}';"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
            Module Dashboard
        </button>

        <div class="relative w-full sm:w-1/2">
            <label for="search" class="sr-only">Zoek modules:</label>
            <input type="text" id="search"
                autocomplete="off"
                placeholder="Zoek op naam of categorie..."
                class="border px-2 py-1 rounded w-full">

            <ul id="search-suggestions"
                class="absolute z-10 bg-white dark:bg-gray-800 border border-gray-300 rounded mt-1 w-full max-h-40 overflow-y-auto hidden">
            </ul>
        </div>
    </div>

    {{-- Filter op categorie --}}
    <form method="GET" action="{{ route('simulatiedashboard') }}" class="mb-4 text-left">
        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Filter op categorie:</label>
        <select name="category" id="category" onchange="this.form.submit()" class="border px-2 py-1 rounded w-full">
            <option value="">Alle categorieën</option>
            @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                {{ ucfirst($cat) }}
            </option>
            @endforeach
        </select>
    </form>

    {{-- Scrollbare modulekaartjes --}}
    <div class="flex-1 overflow-y-auto">
        <div class="grid font-sensitive-grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 pr-1 pb-4">
            @forelse($modules as $module)
            <div class="module-card bg-gray-50 dark:bg-gray-700 p-3 rounded-md transition ring-offset-2 ring-offset-gray-100 dark:ring-offset-gray-900 shadow-sm flex flex-col items-center text-center w-full"
                draggable="true"
                data-module-id="{{ $module->id }}"
                data-name="{{ $module->name }}"
                data-category="{{ $module->category }}">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200">{{ $module->name }}</h3>
                <p class="text-sm text-blue-500 mt-1">Categorie: {{ $module->category }}</p>

                @if(isset($module->image_path))
                <div class="mt-2">
                    <img src="{{ asset('storage/' . $module->image_path) }}"
                        alt="{{ $module->name }}"
                        class="w-14 h-14 object-contain rounded-md pointer-events-none">
                </div>
                @endif
            </div>
            @empty
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow text-center col-span-full">
                <p class="text-gray-500 dark:text-gray-400">Er zijn geen modules beschikbaar.</p>
            </div>
            @endforelse

            {{-- Geen overeenkomsten --}}
            <div id="no-matches-message" class="hidden bg-white dark:bg-gray-800 p-4 rounded-lg shadow text-center col-span-full">
                <p class="text-gray-500 dark:text-gray-400">Geen overeenkomende modules gevonden.</p>
            </div>
        </div>
    </div>