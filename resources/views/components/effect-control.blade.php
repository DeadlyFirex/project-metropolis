@php
$types = [
    'safety' => 'Veiligheid',
    'recreation' => 'Recreatie',
    'climate' => 'Milieukwaliteit',
    'facilities' => 'Voorzieningen',
    'infrastructure' => 'Mobiliteit'
];
@endphp

<h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 text-left">Effecten Beheersen</h2>


    <table class="w-full text-xs text-center table-fixed">
        <thead class="bg-gray-100 text-gray-800">
            <tr>
                <th class="px-2 py-1 border text-left w-20">Module</th>
                @foreach($types as $key => $label)
                    <th class="px-2 py-1 border w-20 whitespace-nowrap">{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($modules as $module)
                <tr class="hover:bg-gray-50">
                    <td class="px-2 py-1 border text-left font-medium text-gray-700">{{ $module->name }}</td>
                    @foreach($types as $key => $label)
                        @php
                            $effect = $module->effects->where('type', $key)->first();
                            $value = $effect?->value ?? 0;
                        @endphp
                        <td class="px-2 py-1 border">
                            <div class="flex flex-col items-center justify-center gap-1">
                                <button
                                    type="button"
                                    class="text-green-600 font-bold text-sm leading-none"
                                    data-action="effect-adjust"
                                    data-module-id="{{ $module->id }}"
                                    data-effect-type="{{ $key }}"
                                    data-delta="1">+</button>

                                <span
                                    class="effect-value font-semibold text-xs {{ $value > 0 ? 'text-green-600' : ($value < 0 ? 'text-red-600' : 'text-gray-700') }}"
                                    data-module="{{ $module->id }}"
                                    data-type="{{ $key }}">
                                    {{ $value > 0 ? '+' . $value : $value }}
                                </span>

                                <button
                                    type="button"
                                    class="text-red-500 font-bold text-sm leading-none"
                                    data-action="effect-adjust"
                                    data-module-id="{{ $module->id }}"
                                    data-effect-type="{{ $key }}"
                                    data-delta="-1">–</button>
                            </div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script src="{{ asset('js/effect-control.js') }}"></script>

