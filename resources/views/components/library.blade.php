<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg text-xl">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Module Library</h2>

            <form method="GET" action="{{ route('simulatiedashboard') }}">
                <select name="category" onchange="this.form.submit()">
                    <option value="">-- Alle categorieën --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                            {{ ucfirst($cat) }}
                        </option>
                    @endforeach
                </select>
            </form>

            <div class="mt-4 space-y-4">
                @forelse($modules as $module)
                    <div class="bg-gray-100 dark:bg-gray-700 p-6 border border-gray-300 dark:border-gray-600 rounded-lg">
                        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                            {{ $module->name ?? 'Untitled Module' }}
                        </h3>


                        <p class="text-blue-500 mt-1">
                            Category: {{ $module->category ?? 'N/A' }}
                        </p>

                        @if(isset($module->image_path))
                            <div class="mt-4">
                                <img src="{{ asset('storage/' . $module->image_path) }}"
                                     alt="{{ $module->name ?? 'Module image' }}"
                                     class="w-16 h-16 object-cover rounded-md mx-auto"
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

                        @if(!empty($module->factors))
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-400 mb-2"></h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(json_decode($module->factors) as $factor)
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow text-center">
                        <p class="text-gray-500 dark:text-gray-400">No modules available at this time.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
