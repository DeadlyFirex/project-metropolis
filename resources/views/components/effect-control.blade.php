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
    // Wait until the DOM is fully loaded
    document.addEventListener("DOMContentLoaded", () => {
        // Get references to key UI elements
        const backBtn = document.getElementById("back-to-calculated-effects");
        const effectView = document.getElementById("effect-view");
        const controlView = document.getElementById("effect-control-view");

        // If all elements exist, set up click handler for the "back" button
        if (backBtn && effectView && controlView) {
            backBtn.addEventListener("click", () => {
                // Hide the control view
                controlView.classList.add("hidden");
                // Show the main effect view
                effectView.classList.remove("hidden");
            });
        }

        // Add click event listeners to all effect-adjust buttons
        document.querySelectorAll('button[data-action="effect-adjust"]').forEach(button => {
            button.addEventListener('click', () => {
                // Get data attributes from the clicked button
                const moduleId = button.getAttribute('data-module-id');
                const effectType = button.getAttribute('data-effect-type');
                const delta = parseInt(button.getAttribute('data-delta'), 10);

                // Find the corresponding span element that displays the effect value
                const span = document.querySelector(
                    `span.effect-value[data-module="${moduleId}"][data-type="${effectType}"]`
                );
                if (!span) return;

                // Get the current value from the span, defaulting to 0
                let currentValue = parseInt(span.textContent.replace('+', '')) || 0;
                const newValue = currentValue + delta;

                // Check if the new value is within the allowed range (-5 to 5)
                if (newValue > 5 || newValue < -5) {
                    alert('Waarde moet tussen -5 en 5 blijven.');
                    return; // Stop further processing if limit is exceeded
                }

            });
        });
    });
</script>
