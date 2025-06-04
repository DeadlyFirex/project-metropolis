<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Simulatie Dashboard') }}
        </h2>
        <button class="bg-blue-500 text-white px-4 py-2 text-sm sm:text-base rounded" id="increaseFontBtn">
            Tekstgrootte Vergroten
        </button>
    </x-slot>

    <div class="w-full bg-gray-100 min-h-screen">
        <div class="flex flex-col gap-6 px-4 sm:px-6 pt-6">

            {{-- Rij 1: City Grid (links) + rechterkolom met Library en Effects --}}
            <div class="flex flex-col lg:flex-row gap-6 items-start">

                {{-- City Grid links --}}
                <main class="w-full max-w-full lg:max-w-[1000px] bg-white p-4 sm:p-6 rounded-2xl shadow">
                    @include('components.city-grid', ['slots' => $slots])
                </main>

                {{-- Rechterkant: Bibliotheek + Calculated Effects --}}
                <div class="w-full lg:flex-1 flex flex-col gap-6">
                    {{-- Module Bibliotheek (boven) --}}
                    <section class="bg-white dark:bg-gray-900 px-4 py-6 rounded-2xl shadow w-full h-[400px] overflow-y-auto">
                        @include('components.library', ['modules' => $modules, 'categories' => $categories])
                    </section>

                    {{-- Calculated Effects (zichtbaar) --}}
                    <section id="effect-view" class="bg-white dark:bg-gray-900 px-4 py-6 rounded-2xl shadow w-full overflow-x-auto">
                        @include('components.calculated-effects', ['slots' => $slots])
                    </section>

                    {{-- Effect Control (verborgen) --}}
                    <section id="effect-control-view" class="hidden bg-white dark:bg-gray-900 px-4 py-6 rounded-2xl shadow w-full overflow-x-auto">
                        @include('components.effect-control', ['modules' => $modules])
                    </section>


                </div>

            </div>



        </div>
    </div>
</x-app-layout>