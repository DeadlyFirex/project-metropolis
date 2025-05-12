<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Module Bibliotheek</h2>

        <form method="GET" action="{{ route('simulatiedashboard') }}">
            <select name="category" onchange="this.form.submit()" class="border px-2 py-1 rounded">
                <option value="">-- Alle categorieën --</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                        {{ ucfirst($cat) }}
                    </option>
                @endforeach
            </select>
        </form>

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($modules as $module)
                <div class="bg-gray-100 dark:bg-gray-700 p-4 border border-gray-300 dark:border-gray-600 rounded-lg">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 text-center">
                        {{ $module->name ?? 'Untitled Module' }}
                    </h3>

                    <p class="text-blue-500 mt-1 text-center">
                        Category: {{ $module->category ?? 'N/A' }}
                    </p>

                    @if (isset($module->image_path))
                        <div class="mt-4">
                            <img src="{{ asset('storage/' . $module->image_path) }}"
                                alt="{{ $module->name ?? 'Module image' }}"
                                class="w-16 h-16 object-cover rounded-md mx-auto" draggable="true"
                                data-module-id="{{ $module->id }}" data-name="{{ $module->name }}"
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
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow text-center">
                    <p class="text-gray-500 dark:text-gray-400">Er zijn geen modules beschikbaar.</p>
                </div>
            @endforelse
        </div>
    </div>
    <button onclick="window.location.href='{{ route('module.index') }}';"
        class="bg-blue-500 text-white px-4 py-2 rounded mt-4">
        Module Dashboard
    </button>
</div>
