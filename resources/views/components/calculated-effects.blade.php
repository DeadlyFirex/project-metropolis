@php
    $effectTypes = [
        'safety' => 'Veiligheid',
        'recreation' => 'Recreatie',
        'climate' => 'Milieukwaliteit',
        'facilities' => 'Voorzieningen',
        'infrastructure' => 'Mobiliteit',
    ];
@endphp

<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Effecten op de grid</h2>

        <div>
            <table class="table-auto border-collapse border border-gray-300 w-full text-center">
                <thead>
                    <tr>
                        <td>Module naam</td>
                        @foreach($effectTypes as $label)
                            <td>{{ $label }}</td>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            @foreach ($slots as $slot)
                                @if($slot->module)
                                    <p>{{ $slot->module->name }}</p>
                                @endif
                            @endforeach
                        </td>

                        @foreach ($effectTypes as $type => $label)
                            <td>
                                @php $total = 0; @endphp
                                @foreach ($slots as $slot)
                                    @if($slot->module)
                                        @foreach ($slot->module->effects as $effect)
                                            @if ($effect->type === $type)
                                                <p>{{ $effect->value }}</p>
                                                @php $total += (int) $effect->value; @endphp
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                                <p><strong>Totaal:</strong> {{ $total }}</p>
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
