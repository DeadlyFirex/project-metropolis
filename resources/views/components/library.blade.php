<div class="h-full flex flex-col bg-white dark:bg-gray-900 px-4 pt-6">

    {{-- Titel --}}
    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 text-left">Module Bibliotheek</h2>
    <div class="mb-4">
        <button onclick="window.location.href='{{ route('module.index') }}';"
                class="bg-blue-500 text-white px-4 py-2 rounded w-auto">
            Module Dashboard
        </button>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('simulatiedashboard') }}" class="mb-4 text-left">
        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Filter op categorie:</label>
        <select name="category" id="category" onchange="this.form.submit()" class="border px-2 py-1 rounded w-full">
            <option value="">-- Alle categorieën --</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                    {{ ucfirst($cat) }}
                </option>
            @endforeach
        </select>
    </form>

    {{-- Module Grid --}}
    <div class="flex-1 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 pr-1">
        @forelse($modules as $module)
            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg shadow-sm flex flex-col items-center text-center max-w-xs w-full max-h-[175px]">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200">
                    {{ $module->name ?? 'Untitled Module' }}
                </h3>

                <p class="text-sm text-blue-500 mt-1">
                    Categorie: {{ $module->category ?? 'N/A' }}
                </p>

                @if(isset($module->image_path))
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $module->image_path) }}"
                             alt="{{ $module->name ?? 'Module image' }}"
                             class="w-14 h-14 object-contain rounded-md"
                             draggable="true"
                             data-module-id="{{ $module->id }}"
                             data-name="{{ $module->name }}"
                             ondragstart="
                                 event.dataTransfer.setData('type', 'module');
                                 event.dataTransfer.setData('module_id', this.dataset.moduleId);
                                 event.dataTransfer.setData('name', this.dataset.name);
                                 event.dataTransfer.setData('img', this.src);
                             ">
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow text-center col-span-full">
                <p class="text-gray-500 dark:text-gray-400">Er zijn geen modules beschikbaar.</p>
            </div>
        @endforelse
    </div>
</div>
