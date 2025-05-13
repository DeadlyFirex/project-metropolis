<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </x-slot>

    <div class="bg-gray-100 min-h-screen p-6">
        <div class="flex gap-6 items-start">

           
            <div class="w-[300px] bg-white p-4 rounded-2xl shadow">
                @include('components.library', ['modules' => $modules, 'categories' => $categories])
            </div>

           
            <div class="flex-1 bg-white p-6 rounded-2xl shadow">
                @include('components.city-grid', ['slots' => $slots])
            </div>

            
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
