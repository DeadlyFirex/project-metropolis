<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Simulatie Dashboard') }}
            </h2>
            <div class="flex gap-2">
                <button class="bg-blue-500 text-white px-4 py-2 text-sm sm:text-base rounded" id="increaseFontBtn">
                    Tekstgrootte Vergroten
                </button>
                <button onclick="window.print()" class="bg-green-600 text-white px-4 py-2 text-sm sm:text-base rounded">
                    Download als PDF
                </button>
            </div>
        </div>
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

   {{-- Printbare versie van het rapport, wordt alleen zichtbaar tijdens afdrukken (window.print) --}}
<div id="printable-area" class="hidden print:block absolute left-[-9999px] top-0 w-full">
    <div class="p-6 bg-white text-black w-full">
        
        {{-- Titel van het rapport --}}
        <h1 class="text-2xl font-bold mb-6">Simulatie Rapport</h1>

        {{-- Toon de Metropolis Grid zoals die nu staat --}}
        @include('components.city-grid', ['slots' => $slots])

        {{-- Tabel met berekende effecten per module --}}
        <div class="mt-8 overflow-visible w-full">
            {{-- Titel boven de effecttabel --}}
            <div class="text-xl font-semibold mb-2">Effecten op de Grid</div>

            {{-- Effectentabel met alle modules en hun waarden --}}
            <div style="overflow: visible !important;">
                <table class="w-full text-xs text-left border-collapse border border-gray-300">

                    {{-- Tabelkoppen: effectcategorieën --}}
                    <thead class="bg-gray-100 text-gray-800">
                        <tr>
                            <th class="px-2 py-1 border border-gray-300">Module</th>
                            <th class="px-2 py-1 border border-gray-300">Veiligheid</th>
                            <th class="px-2 py-1 border border-gray-300">Recreatie</th>
                            <th class="px-2 py-1 border border-gray-300">Milieukwaliteit</th>
                            <th class="px-2 py-1 border border-gray-300">Voorzieningen</th>
                            <th class="px-2 py-1 border border-gray-300">Mobiliteit</th>
                            <th class="px-2 py-1 border border-gray-300">Kwaliteit van Leven</th>
                        </tr>
                    </thead>

                    {{-- Inhoud van de tabel: per module de effectwaarden ophalen --}}
                    <tbody class="bg-white">
                        @php 
                            // Initialiseer totaalwaarden voor elk effecttype
                            $totals = ['safety' => 0, 'recreation' => 0, 'climate' => 0, 'facilities' => 0, 'infrastructure' => 0]; 
                        @endphp

                        @foreach ($slots as $slot)
                            @if($slot->module)
                                @php
                                    // Verzamel de waarden van elk effecttype voor de huidige module
                                    $effects = [
                                        'safety' => $slot->module->effects->firstWhere('type', 'safety')?->value ?? 0,
                                        'recreation' => $slot->module->effects->firstWhere('type', 'recreation')?->value ?? 0,
                                        'climate' => $slot->module->effects->firstWhere('type', 'climate')?->value ?? 0,
                                        'facilities' => $slot->module->effects->firstWhere('type', 'facilities')?->value ?? 0,
                                        'infrastructure' => $slot->module->effects->firstWhere('type', 'infrastructure')?->value ?? 0,
                                    ];
                                    
                                    // Tel deze op bij de totalen
                                    foreach ($effects as $type => $val) {
                                        $totals[$type] += $val;
                                    }

                                    // Totale kwaliteit van leven berekenen als som van alle effecten
                                    $qol = array_sum($effects);
                                @endphp

                                {{-- Rijtje voor deze module --}}
                                <tr>
                                    <td class="border px-2 py-1">{{ $slot->module->name }}</td>
                                    <td class="border px-2 py-1">{{ $effects['safety'] }}</td>
                                    <td class="border px-2 py-1">{{ $effects['recreation'] }}</td>
                                    <td class="border px-2 py-1">{{ $effects['climate'] }}</td>
                                    <td class="border px-2 py-1">{{ $effects['facilities'] }}</td>
                                    <td class="border px-2 py-1">{{ $effects['infrastructure'] }}</td>
                                    <td class="border px-2 py-1 font-semibold">{{ $qol }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>

                    {{-- Voetrij met totaalsom van alle effecten over alle modules --}}
                    <tfoot>
                        @php 
                            $totalQol = array_sum($totals); 
                        @endphp
                        <tr class="bg-gray-200 font-bold">
                            <td class="border px-2 py-1">Totaal</td>
                            <td class="border px-2 py-1">{{ $totals['safety'] }}</td>
                            <td class="border px-2 py-1">{{ $totals['recreation'] }}</td>
                            <td class="border px-2 py-1">{{ $totals['climate'] }}</td>
                            <td class="border px-2 py-1">{{ $totals['facilities'] }}</td>
                            <td class="border px-2 py-1">{{ $totals['infrastructure'] }}</td>
                            <td class="border px-2 py-1">{{ $totalQol }}</td>
                        </tr>
                    </tfoot>

                </table>
            </div>
        </div>

    </div>
</div>

</x-app-layout>
