@php
$types = ['safety', 'recreation', 'climate', 'facilities', 'infrastructure'];
@endphp

<h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Effecten Beheersen
</h2>

<table class="table-auto border-collapse border border-gray-300 w-full text-center">
    <thead>
        <tr>
            <th class="border p-2 bg-gray-200">Module</th>
            @foreach($types as $type)
                <th class="border p-2 bg-gray-200">{{ ucfirst($type) }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($modules as $module)
            <tr>
                <th class="border p-2 bg-gray-100">{{ $module->name }}</th>
                @foreach($types as $type)
                    @php
                        $effect = $module->effects->where('type', $type)->first();
                        $value = $effect?->value ?? 0;
                    @endphp
                    <td class="border p-2">
                        <div class="flex flex-col items-center justify-center">
                            <button
                                type="button"
                                class="text-green-500 font-bold text-xl leading-none"
                                data-action="effect-adjust"
                                data-module-id="{{ $module->id }}"
                                data-effect-type="{{ $type }}"
                                data-delta="1"
                            >+</button>

                            <span
                                class="text-lg text-gray-700 my-1 effect-value"
                                data-module="{{ $module->id }}"
                                data-type="{{ $type }}"
                            >{{ $value }}</span>

                            <button
                                type="button"
                                class="text-red-500 font-bold text-xl leading-none"
                                data-action="effect-adjust"
                                data-module-id="{{ $module->id }}"
                                data-effect-type="{{ $type }}"
                                data-delta="-1"
                            >–</button>
                        </div>
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
