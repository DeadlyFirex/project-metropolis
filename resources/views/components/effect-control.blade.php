@php
    $types = [
        'safety' => 'Veiligheid',
        'recreation' => 'Recreatie',
        'climate' => 'Milieukwaliteit',
        'facilities' => 'Voorzieningen',
        'infrastructure' => 'Mobiliteit',
    ];
@endphp

<h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 text-left">Effecten Beheren</h2>

<div class="mb-4 text-left">
    <button id="back-to-calculated-effects"
        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-3 py-1 rounded w-full sm:w-auto">
        ← Terug naar overzicht
    </button>
</div>

<div class="overflow-x-auto">
    <table class="w-full text-xs text-center table-fixed min-w-[600px]">
        <thead class="bg-gray-100 text-gray-800">
            <tr>
                <th class="px-2 py-1 border text-left w-24">Module</th>
                @foreach ($types as $key => $label)
                    <th class="px-2 py-1 border w-20 whitespace-nowrap">{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($all_modules as $module)
                <tr class="hover:bg-gray-50">
                    <td class="px-2 py-1 border text-left font-medium text-gray-700 break-words">{{ $module->name }}
                    </td>
                    @foreach ($types as $key => $label)
                        @php
                            $effect = $module->effects->where('type', $key)->first();
                            $value = $effect?->value ?? 0;
                        @endphp
                        <td class="px-1 py-1 border">
                            <div class="flex flex-col items-center justify-center gap-1">
                                {{-- Buttons iets kleiner voor mobiel --}}
                                <button type="button" class="text-green-600 font-bold text-sm leading-none"
                                    data-action="effect-adjust" data-module-id="{{ $module->id }}"
                                    data-effect-type="{{ $key }}" data-delta="1">+</button>

                                <span
                                    class="effect-value font-semibold text-xs {{ $value > 0 ? 'text-green-600' : ($value < 0 ? 'text-red-600' : 'text-gray-700') }}"
                                    data-module="{{ $module->id }}" data-type="{{ $key }}">
                                    {{ $value > 0 ? '+' . $value : $value }}
                                </span>

                                <button type="button" class="text-red-500 font-bold text-sm leading-none"
                                    data-action="effect-adjust" data-module-id="{{ $module->id }}"
                                    data-effect-type="{{ $key }}" data-delta="-1">–</button>
                            </div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- <script src="{{ asset('js/effect-control.js') }}"></script> --}}

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const backBtn = document.getElementById("back-to-calculated-effects");
        const effectView = document.getElementById("effect-view");
        const controlView = document.getElementById("effect-control-view");

        if (backBtn && effectView && controlView) {
            backBtn.addEventListener("click", () => {
                controlView.classList.add("hidden");
                effectView.classList.remove("hidden");
            });
        }

        document.querySelectorAll('button[data-action="effect-adjust"]').forEach(button => {
            button.addEventListener('click', async () => {
                const moduleId = button.getAttribute('data-module-id');
                const effectType = button.getAttribute('data-effect-type');
                const delta = parseInt(button.getAttribute('data-delta'), 10);

                const span = document.querySelector(
                    `span.effect-value[data-module="${moduleId}"][data-type="${effectType}"]`
                );
                if (!span) return;

                let currentValue = parseInt(span.textContent.replace('+', '')) || 0;
                const newValue = currentValue + delta;

                if (newValue > 5 || newValue < -5) {
                    alert('Waarde moet tussen -5 en 5 blijven.');
                    return;
                }

                // Stuur update naar server
                try {
                    const response = await fetch(
                        `/effects/module/${moduleId}/${effectType}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify({
                                value: newValue
                            })
                        });

                    if (!response.ok) {
                        const errorData = await response.json();
                        alert('Fout bij opslaan: ' + (errorData.error || 'Onbekende fout'));
                        return;
                    }

                    // Update de waarde in de UI
                    span.textContent = newValue > 0 ? '+' + newValue : newValue;
                    span.classList.remove('text-green-600', 'text-red-600',
                    'text-gray-700');

                    if (newValue > 0) {
                        span.classList.add('text-green-600');
                    } else if (newValue < 0) {
                        span.classList.add('text-red-600');
                    } else {
                        span.classList.add('text-gray-700');
                    }

                } catch (error) {
                    alert('Netwerkfout: ' + error.message);
                }
            });
        });
    });
</script>
