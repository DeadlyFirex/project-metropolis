<?php

$simTime   = $simTime ?? now()->format('H:i:s');
$simCarbon = \Carbon\Carbon::createFromFormat('H:i:s', $simTime);

$effectTypes = [
    'safety' => 'Veiligheid',
    'recreation' => 'Recreatie',
    'climate' => 'Milieukwaliteit',
    'facilities' => 'Voorzieningen',
    'infrastructure' => 'Mobiliteit',
];

// Initialize overall totals (for the combined total row at the very bottom)
$overallModuleTotals = array_fill_keys(array_keys($effectTypes), 0);
$overallPrimaryEventTotals = array_fill_keys(array_keys($effectTypes), 0);
$overallAdjacentEventTotals = array_fill_keys(array_keys($effectTypes), 0);

$overallModuleQol = 0;
$overallPrimaryEventQol = 0;
$overallAdjacentQol = 0;

// Data structures to store effects aggregated by Module and EventType
$moduleEffectTotals = []; // Stores effects for each unique module
$moduleIdToName = []; // Maps module ID to module name for JavaScript updates
$eventPrimaryEffectTotalsByType = []; // Stores primary effects for each EventType
$eventAdjacentEffectTotalsByType = []; // Stores adjacent effects originating from each EventType

// To collect unique module and eventType objects for iteration in tfoot
$uniqueModules = [];
$uniqueEventTypes = [];

// Helper to get adjacent slots (mimicking EventController logic directly in Blade)
$gridWidth = 4;
$gridHeight = 3;

function getAdjacentSlotsForBlade($slotId, $allSlots, $gridWidth, $gridHeight) {
    $currentSlot = null;
    foreach ($allSlots as $s) {
        if ($s->id == $slotId) {
            $currentSlot = $s;
            break;
        }
    }

    if (!$currentSlot) {
        return collect();
    }

    $adjacentIds = [];
    $currentPos = $slotId; // Using 1-indexed slot ID

    $row = ceil($currentPos / $gridWidth);
    $col = ($currentPos - 1) % $gridWidth + 1;

    // Check 8 adjacent cells
    // Left
    if ($col > 1) $adjacentIds[] = $currentPos - 1;
    // Right
    if ($col < $gridWidth) $adjacentIds[] = $currentPos + 1;
    // Up
    if ($row > 1) $adjacentIds[] = $currentPos - $gridWidth;
    // Down
    if ($row < $gridHeight) $adjacentIds[] = $currentPos + $gridWidth;
    // Top-left
    if ($row > 1 && $col > 1) $adjacentIds[] = $currentPos - $gridWidth - 1;
    // Top-right
    if ($row > 1 && $col < $gridWidth) $adjacentIds[] = $currentPos - $gridWidth + 1;
    // Bottom-left
    if ($row < $gridHeight && $col > 1) $adjacentIds[] = $currentPos + $gridWidth - 1;
    // Bottom-right
    if ($row < $gridHeight && $col < $gridWidth) $adjacentIds[] = $currentPos + $gridWidth + 1;

    $adjacentSlotsCollection = collect();
    foreach ($allSlots as $s) {
        if (in_array($s->id, $adjacentIds)) {
            $adjacentSlotsCollection->push($s);
        }
    }
    return $adjacentSlotsCollection;
}

// --- Pass 1: Aggregate effects for Modules and EventTypes ---
foreach ($slots as $slot) {
    // Sla verlopen events volledig over
    if (
        $slot->event
        && $slot->event->end_time
        && \Carbon\Carbon::createFromFormat('H:i:s', $slot->event->end_time)
            ->lte($simCarbon)
    ) {
        continue;
    }

    // Collect all unique event types and modules
    if ($slot->event && $slot->event->eventType) {
        $eventTypeId = $slot->event->eventType->id;
        if (!isset($uniqueEventTypes[$eventTypeId])) {
            $uniqueEventTypes[$eventTypeId] = $slot->event->eventType;
        }
    }
    if ($slot->module) {
        $moduleId = $slot->module->id;
        if (!isset($uniqueModules[$moduleId])) {
            $uniqueModules[$moduleId] = $slot->module;
            $moduleIdToName[$moduleId] = $slot->module->name;
        }
    }

    // --- Module Effects Aggregation ---
    if ($slot->module) {
        $moduleName = $slot->module->name;
        $moduleId = $slot->module->id;

        if (!isset($moduleEffectTotals[$moduleId])) {
            $moduleEffectTotals[$moduleId] = array_fill_keys(array_keys($effectTypes), 0);
        }
        foreach ($slot->module->effects as $effect) {
            if ($effect->value !== 0) {
                $overallModuleTotals[$effect->type] += $effect->value;
                $overallModuleQol += $effect->value;
                $moduleEffectTotals[$moduleId][$effect->type] += $effect->value;
            }
        }
    }

    // --- Primary Event Effects Aggregation ---
    if ($slot->event && $slot->event->eventType) {
        $eventTypeName = $slot->event->eventType->name;
        if (!isset($eventPrimaryEffectTotalsByType[$eventTypeName])) {
            $eventPrimaryEffectTotalsByType[$eventTypeName] = array_fill_keys(array_keys($effectTypes), 0);
        }
        foreach ($slot->event->eventType->effects as $effect) {
            if ($effect->value !== 0 && $effect->is_primary_effect) {
                $overallPrimaryEventTotals[$effect->type] += $effect->value;
                $overallPrimaryEventQol += $effect->value;
                $eventPrimaryEffectTotalsByType[$eventTypeName][$effect->type] += $effect->value;
            }
        }
    }

    // --- Adjacent Event Effects Aggregation ---
    // ONLY if current slot has a module
    if ($slot->module) {
        $adjacentSlotsForCurrentSlot = getAdjacentSlotsForBlade($slot->id, $slots, $gridWidth, $gridHeight);

        foreach ($adjacentSlotsForCurrentSlot as $adjSlot) {
            // Only consider adjacent slots that have BOTH an event AND a module
            if (
                $adjSlot->id != $slot->id
                && $adjSlot->event
                && $adjSlot->event->eventType
                && $adjSlot->module
                && $adjSlot->event->end_time
                && \Carbon\Carbon::createFromFormat('H:i:s', $adjSlot->event->end_time)
                    ->gt($simCarbon)
            ) {
                $adjEventTypeName = $adjSlot->event->eventType->name;

                if (!isset($eventAdjacentEffectTotalsByType[$adjEventTypeName])) {
                    $eventAdjacentEffectTotalsByType[$adjEventTypeName] = array_fill_keys(array_keys($effectTypes), 0);
                }

                foreach ($adjSlot->event->eventType->effects as $effect) {
                    if ($effect->value !== 0 && $effect->is_adjacent_effect) {
                        $valueWithFactor = $effect->value;
                        $overallAdjacentEventTotals[$effect->type] += $valueWithFactor;
                        $overallAdjacentQol += $valueWithFactor;
                        $eventAdjacentEffectTotalsByType[$adjEventTypeName][$effect->type] += $valueWithFactor;
                    }
                }
            }
        }
    }
}


// Sort unique modules and event types by name for consistent display
usort($uniqueModules, function($a, $b) {
    return strcmp($a->name, $b->name);
});
usort($uniqueEventTypes, function($a, $b) {
    return strcmp($a->name, $b->name);
});
?>

<div class="py-2 px-2" id="effect-view">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2 text-left">Effecten op de grid</h2>
    <div class="mt-2 mb-4">
        <button
            id="swap-to-effect-control"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
            Effecten beheren
        </button>
    </div>

    <div class="border-gray-200 overflow-x-auto">
        <table id="calculated-effects-table" class="text-xs text-center min-w-max border-collapse">
            <thead class="bg-gray-100 text-gray-800">
            <tr>
                <th class="px-1 py-1 border border-gray-300 text-left w-32">Type / Item</th>
                @foreach ($effectTypes as $type => $label)
                    <th class="px-1 py-1 border border-gray-300 w-24">{{ $label }}</th>
                @endforeach
                <th class="px-1 py-1 border border-gray-300 w-24">Kwaliteit van Leven</th>
            </tr>
            </thead>
            <tfoot class="bg-gray-50 text-gray-800 font-bold">
            {{-- Module Section --}}
            <tr class="bg-gray-100">
                <td class="px-1 py-1 border border-gray-300 text-left font-semibold" colspan="{{ count($effectTypes) + 2 }}">
                    Module Overzicht
                </td>
            </tr>
            @foreach ($uniqueModules as $module)
                <tr data-module-id="{{ $module->id }}">
                    <td class="px-1 py-1 border border-gray-300 text-left pl-4">Module: {{ $module->name }}</td>
                    @php
                        $moduleQolForType = 0;
                    @endphp
                    @foreach ($effectTypes as $type => $label)
                        @php
                            $value = $moduleEffectTotals[$module->id][$type] ?? 0;
                            $moduleQolForType += $value;
                        @endphp
                        <td class="px-2 py-1 border border-gray-300">
                            <span class="{{ $value < 0 ? 'text-red-600' : ($value > 0 ? 'text-green-600' : 'text-gray-800') }}"
                                  data-module-id="{{ $module->id }}" data-effect-type="{{ $type }}">
                                {{ number_format($value, 0, ',', '.') > 0 ? '+' . number_format($value, 0, ',', '.') : number_format($value, 0, ',', '.') }}
                            </span>
                        </td>
                    @endforeach
                    <td class="px-1 py-1 border border-gray-300 {{ $moduleQolForType < 0 ? 'text-red-600' : ($moduleQolForType > 0 ? 'text-green-600' : 'text-gray-800') }}"
                        data-module-id="{{ $module->id }}" data-qol="true">
                        {{ number_format($moduleQolForType, 0, ',', '.') > 0 ? '+' . number_format($moduleQolForType, 0, ',', '.') : number_format($moduleQolForType, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach

            {{-- Event Section --}}
            <tr class="bg-blue-100">
                <td class="px-1 py-1 border border-gray-300 text-left font-semibold" colspan="{{ count($effectTypes) + 2 }}">
                    Evenementen Overzicht
                </td>
            </tr>
            @foreach ($uniqueEventTypes as $eventType)
                <tr class="bg-blue-50">
                    <td class="px-1 py-1 border border-gray-300 text-left font-semibold" colspan="{{ count($effectTypes) + 2 }}">
                        Evenement Type: {{ $eventType->name }}
                    </td>
                </tr>
                <tr data-event-type="{{ $eventType->name }}" data-effect-category="primary">
                    <td class="px-1 py-1 border border-gray-300 text-left pl-4">Primaire Effecten</td>
                    @php
                        $primaryQolForType = 0;
                    @endphp
                    @foreach ($effectTypes as $type => $label)
                        @php
                            $value = $eventPrimaryEffectTotalsByType[$eventType->name][$type] ?? 0;
                            $primaryQolForType += $value;
                        @endphp
                        <td class="px-2 py-1 border border-gray-300">
                            <span class="{{ $value < 0 ? 'text-red-600' : ($value > 0 ? 'text-green-600' : 'text-gray-800') }}">
                                {{ number_format($value, 0, ',', '.') > 0 ? '+' . number_format($value, 0, ',', '.') : number_format($value, 0, ',', '.') }}
                            </span>
                        </td>
                    @endforeach
                    <td class="px-1 py-1 border border-gray-300 {{ $primaryQolForType < 0 ? 'text-red-600' : ($primaryQolForType > 0 ? 'text-green-600' : 'text-gray-800') }}">
                        {{ number_format($primaryQolForType, 0, ',', '.') > 0 ? '+' . number_format($primaryQolForType, 0, ',', '.') : number_format($primaryQolForType, 0, ',', '.') }}
                    </td>
                </tr>
                <tr data-event-type="{{ $eventType->name }}" data-effect-category="adjacent">
                    <td class="px-1 py-1 border border-gray-300 text-left pl-4">Aangrenzende Effecten</td>
                    @php
                        $adjacentQolForType = 0;
                    @endphp
                    @foreach ($effectTypes as $type => $label)
                        @php
                            $value = $eventAdjacentEffectTotalsByType[$eventType->name][$type] ?? 0;
                            $adjacentQolForType += $value;
                        @endphp
                        <td class="px-2 py-1 border border-gray-300">
                            <span class="{{ $value < 0 ? 'text-red-600' : ($value > 0 ? 'text-green-600' : 'text-gray-800') }}">
                                {{ number_format($value, 0, ',', '.') > 0 ? '+' . number_format($value, 0, ',', '.') : number_format($value, 0, ',', '.') }}
                            </span>
                        </td>
                    @endforeach
                    <td class="px-1 py-1 border border-gray-300 {{ $adjacentQolForType < 0 ? 'text-red-600' : ($adjacentQolForType > 0 ? 'text-green-600' : 'text-gray-800') }}">
                        {{ number_format($adjacentQolForType, 0, ',', '.') > 0 ? '+' . number_format($adjacentQolForType, 0, ',', '.') : number_format($adjacentQolForType, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach

            {{-- Final Combined Total Row --}}
            <tr class="bg-gray-300">
                <td class="px-1 py-1 border border-gray-300 text-left">Gecombineerd Totaal</td>
                @php
                    $overallCombinedQol = 0;
                @endphp
                @foreach ($effectTypes as $type => $label)
                    @php
                        $combinedTotalForType = $overallModuleTotals[$type] + $overallPrimaryEventTotals[$type] + $overallAdjacentEventTotals[$type];
                        $overallCombinedQol += $combinedTotalForType;
                    @endphp
                    <td class="px-2 py-1 border border-gray-300">
                        <span class="effect-cell {{ $combinedTotalForType < 0 ? 'text-red-600' : ($combinedTotalForType > 0 ? 'text-green-600' : 'text-gray-800') }}">
                            {{ number_format($combinedTotalForType, 0, ',', '.') > 0 ? '+' . number_format($combinedTotalForType, 0, ',', '.') : number_format($combinedTotalForType, 0, ',', '.') }}
                        </span>
                    </td>
                @endforeach
                <td class="px-1 py-1 border border-gray-300 font-semibold {{ $overallCombinedQol < 0 ? 'text-red-600' : ($overallCombinedQol > 0 ? 'text-green-600' : 'text-gray-800') }}">
                    {{ number_format($overallCombinedQol, 0, ',', '.') > 0 ? '+' . number_format($overallCombinedQol, 0, ',', '.') : number_format($overallCombinedQol, 0, ',', '.') }}
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>

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
