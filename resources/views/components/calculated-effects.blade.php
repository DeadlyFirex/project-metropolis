@php
    $effectTypes = [
        'safety' => 'Veiligheid',
        'recreation' => 'Recreatie',
        'climate' => 'Milieukwaliteit',
        'facilities' => 'Voorzieningen',
        'infrastructure' => 'Mobiliteit',
    ];
@endphp

<div class="py-2 px-2">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 text-left">Effecten op de grid</h2>

    <div class=" border-gray-200 overflow-x-auto">
        <table id="calculated-effects-table" class="w-full text-xs text-center table-fixed border-collapse">
            <thead class="bg-gray-100 text-gray-800">
                <tr>
                    <th class="px-2 py-1 border border-gray-300 text-left w-32">Module naam</th>
                    @foreach($effectTypes as $label)
                        <th class="px-2 py-1 border border-gray-300 w-20 whitespace-nowrap">{{ $label }}</th>
                    @endforeach
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
                        </tr>
                    @endif
                @endforeach
            </tbody>

            <tfoot>
                <tr class="bg-gray-200 font-bold">
                    <td class="px-2 py-1 border border-gray-300 text-left">Totaal:</td>
                    @foreach ($effectTypes as $type => $label)
                        @php $total = $totals[$type]; @endphp
                        <td class="px-2 py-1 border border-gray-300" data-value="{{ $total }}">
                            <span class="effect-cell {{ $total < 0 ? 'text-red-600' : ($total > 0 ? 'text-green-600' : 'text-gray-800') }}"
                                  data-type="{{ $type }}">
                                {{ $total > 0 ? '+' . $total : $total }}
                            </span>
                        </td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script src="{{ asset('js/effect-flash.js') }}"></script>
