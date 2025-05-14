<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Simulatie Dashboard') }}
        </h2>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </x-slot>

    <div class="bg-gray-100 min-h-screen">
        <div class="flex gap-6 items-start pt-6 pl-0 pr-6">


            <!-- Sidebar Module Bibliotheek -->
            <div class="w-[300px] sticky top-[5rem] h-[calc(100vh-5rem)] bg-white dark:bg-gray-900 overflow-y-auto border-r px-4 pt-6 pb-12">
                @include('components.library', ['modules' => $modules, 'categories' => $categories])
            </div>

            <!-- Grid -->
            <div class="flex-1 bg-white p-6 rounded-2xl shadow">
                @include('components.city-grid', ['slots' => $slots])
            </div>

            <!-- Effectenpanel -->
            <div class="w-[600px] flex flex-col gap-6">
                <div class="bg-white p-6 rounded-2xl shadow">
                    @include('components.calculated-effects', ['slots' => $slots])
                </div>

                <div class="bg-white p-6 rounded-2xl shadow">
                    @include('components.effect-control', ['modules' => $modules])
                </div>
            </div>

        </div>
    </div>
</x-app-layout>