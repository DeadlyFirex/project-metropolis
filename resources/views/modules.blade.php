<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Available Modules') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg text-xl">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Module Library</h2>
                <div class="mt-4 space-y-4">
                    @forelse($modules as $module)
                        <div class="bg-gray-100 dark:bg-gray-700 p-6 border border-gray-300 dark:border-gray-600 rounded-lg">
                            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ $module->name ?? 'Untitled Module' }}</h3>
                            <p class="text-gray-600 dark:text-gray-300 mt-2">{{ $module->description ?? 'No description available' }}</p>
                            <p class="text-blue-500 mt-1">Category: {{ $module->category ?? 'N/A' }}</p>
                            @if(isset($module->image_path))
                                <div class="mt-4">
                                    <img src="{{ asset('storage/' . $module->image_path) }}"
                                         alt="{{ $module->name ?? 'Module image' }}"
                                         class="w-32 h-32 object-cover rounded-md mx-auto">
                                </div>
                            @endif
                            @if(!empty($module->factors))
                                <div class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Includes:</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(json_decode($module->factors) as $factor)
                                            <span class="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded">{{ $factor }}</span>
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
</x-app-layout>
