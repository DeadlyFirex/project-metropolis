<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Simulatie Dashboard') }}
        </h2>
    </x-slot>

    <div class="w-full bg-gray-100 min-h-screen">
        <div class="flex flex-col gap-6 px-6 pt-6">

            {{-- Rij 1: Sidebar + City Grid --}}
            <div class="flex flex-row items-start gap-6">

                {{-- Sidebar (breder + scrollable) --}}
                <aside class="w-[760px] h-[675px] top-[5rem] min-h-[600px] overflow-y-auto bg-white dark:bg-gray-900 border-r px-4 py-6 shrink-0 rounded-2xl shadow">
                    @include('components.library', ['modules' => $modules, 'categories' => $categories])
                </aside>

                {{-- City Grid (beperkte breedte, niet volledige flex-1) --}}
                <main class="flex-1 max-w-[1000px] h-[675px] bg-white p-6 rounded-2xl shadow min-h-[600px]">
                    @include('components.city-grid', ['slots' => $slots])
                </main>
            </div>

            {{-- Rij 2: Effecten --}}
            <div class="flex flex-col lg:flex-row gap-6">
                <div class="bg-white p-6 rounded-2xl shadow w-full lg:w-1/2">
                    @include('components.calculated-effects', ['slots' => $slots])
                </div>
                <div class="bg-white p-6 rounded-2xl shadow w-full lg:w-1/2">
                    @include('components.effect-control', ['modules' => $modules])
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
