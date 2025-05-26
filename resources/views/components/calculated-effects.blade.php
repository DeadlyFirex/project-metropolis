@php
    $effectTypes = [
        'safety' => 'Veiligheid',
        'recreation' => 'Recreatie',
        'climate' => 'Milieukwaliteit',
        'facilities' => 'Voorzieningen',
        'infrastructure' => 'Mobiliteit',
    ];
@endphp
{{--TODO: Check if this is necessary--}}
{{--<div class="py-2 px-2" id="effect-view">--}}
{{--    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2 text-left">Effecten op de grid</h2>--}}

{{--    <div class="mt-2 mb-4">--}}
{{--        <button--}}
{{--            id="swap-to-effect-control"--}}
{{--            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">--}}
{{--            Effecten beheren--}}
{{--        </button>--}}
{{--    </div>--}}

    <div class=" border-gray-200 overflow-x-auto">
        <table id="calculated-effects-table" class="text-xs text-center min-w-max border-collapse">
            <thead class="bg-gray-100 text-gray-800">
                <tr>
                    <th class="px-1 py-1 border border-gray-300 text-left w-32">Module</th>
                    @foreach ($effectTypes as $label)
                        <th class="px-1 py-1 border border-gray-300 w-20 whitespace-nowrap">{{ $label }}</th>
                    @endforeach
                    <th class="px-1 py-1 border border-gray-300 w-20 whitespace-nowrap">Kwaliteit van Leven</th>
                </tr>
            </thead>

            <tbody class="bg-white">
                @php $totals = array_fill_keys(array_keys($effectTypes), 0); @endphp

                @foreach ($slots as $slot)
                @if($slot->module)
                <tr class="hover:bg-gray-50" data-module-id="{{ $slot->module->id }}">
                    <td class="px-2 py-1 border border-gray-300 text-left font-medium text-gray-700">
                        {{ $slot->module->name }}
                    </td>

                    @foreach ($effectTypes as $type => $label)
                    @php
                    $value = $slot->module->effects->firstWhere('type', $type)?->value ?? 0;
                    $totals[$type] += $value;
                    @endphp
                    <td class="px-2 py-1 border border-gray-300" data-value="{{ $value }}">
                        <span class="effect-cell font-semibold {{ $value < 0 ? 'text-red-600' : ($value > 0 ? 'text-green-600' : 'text-gray-700') }}"
                            data-type="{{ $type }}">
                            {{ $value > 0 ? '+' . $value : $value }}
                        </span>
                    </td>
                    @endforeach
{{--                    TODO: Check if this is necessary--}}
                    @php
                        $qol = $slot->module->effects->sum('value');
                    @endphp
                    <td
                        class="px-1 py-1 border border-gray-300 font-semibold {{ $qol < 0 ? 'text-red-600' : ($qol > 0 ? 'text-green-600' : 'text-gray-700') }}">
                        {{ $qol > 0 ? '+' . $qol : $qol }}
                    </td>
{{-- END --}}
                </tr>
                @endif
                @endforeach
            </tbody>

            <tfoot>
                <tr class="bg-gray-200 font-bold">
                    <td class="px-1 py-1 border border-gray-300 text-left">Totaal:</td>
                    @foreach ($effectTypes as $type => $label)
                    @php $total = $totals[$type]; @endphp
                    <td class="px-2 py-1 border border-gray-300" data-value="{{ $total }}">
                        <span class="effect-cell {{ $total < 0 ? 'text-red-600' : ($total > 0 ? 'text-green-600' : 'text-gray-800') }}"
                            data-type="{{ $type }}">
                            {{ $total > 0 ? '+' . $total : $total }}
                        </span>
                    </td>
                    @endforeach
                    @php
                        $totalQol = array_sum($totals);
                    @endphp
                    <td
                        class="px-1 py-1 border border-gray-300 font-semibold {{ $totalQol < 0 ? 'text-red-600' : ($totalQol > 0 ? 'text-green-600' : 'text-gray-800') }}">
                        {{ $totalQol > 0 ? '+' . $totalQol : $totalQol }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script src="{{ asset('js/effect-flash.js') }}"></script>
{{-- TODO: Check if this is necessary--}}
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const swapBtn = document.getElementById("swap-to-effect-control");
        const viewPanel = document.getElementById("effect-view");
        const controlPanel = document.getElementById("effect-control-view");

        if (swapBtn && viewPanel && controlPanel) {
            swapBtn.addEventListener("click", () => {
                viewPanel.classList.add("hidden");
                controlPanel.classList.remove("hidden");
            });
        }
    });
</script>
{{-- END --}}
