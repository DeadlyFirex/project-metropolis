<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="flex justify-center bg-gray-100">
        <div class="w-full flex space-x-8 p-8 justify-center">

            <div class="w-[600px] bg-white p-8 rounded-3xl shadow-lg text-center text-xl">
                @include('components.city-grid', ['slots' => $slots])
            </div>

            <div class="w-[1000px] bg-white p-8 rounded-3xl shadow-lg text-center text-xl">
                @include('components.library', ['modules' => $modules, 'categories' => $categories])
            </div>

        </div>
    </div>

</x-app-layout>