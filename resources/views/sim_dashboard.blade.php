<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Simulatie Dashboard') }}
        </h2>
    </x-slot>

    {{-- Volledige breedtecontainer buiten max-w beperkingen --}}
    <div class="w-full bg-gray-100 min-h-screen">
        <div class="flex flex-row items-start gap-6 px-6 pt-6">

            {{-- Sidebar: Modulebibliotheek --}}
            <aside class="w-[280px] sticky top-[5rem] h-[calc(100vh-5rem)] overflow-y-auto bg-white dark:bg-gray-900 border-r px-4 py-6 shrink-0 rounded-2xl shadow">
                @include('components.library', ['modules' => $modules, 'categories' => $categories])
            </aside>

            {{-- Midden: City Grid --}}
            <main class="flex-1 bg-white p-6 rounded-2xl shadow overflow-auto min-h-[600px]">
                @include('components.city-grid', ['slots' => $slots])
            </main>

            {{-- Rechterkolom: Effecten --}}
            <aside class="w-[600px] flex flex-col gap-6 shrink-0">
                <div class="bg-white p-6 rounded-2xl shadow">
                    @include('components.calculated-effects', ['slots' => $slots])
                </div>
                <div class="bg-white p-6 rounded-2xl shadow">
                    @include('components.effect-control', ['modules' => $modules])
                </div>
            </aside>

        </div>
    </div>
</x-app-layout>
