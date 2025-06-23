const cols = 4;

/**
 * Utility to get a specific city grid cell by row and column.
 */
function getCell(row, col) {
    return document.querySelector(`td.city-cell[data-row="${row}"][data-col="${col}"]`);
}

// Attach hover listeners to all city slots
document.querySelectorAll('.city-slot').forEach(slot => {
    slot.addEventListener('mouseenter', () => {
        const parentTd = slot.closest('td.city-cell');
        const row = parseInt(parentTd.dataset.row, 10);
        const col = parseInt(parentTd.dataset.col, 10);

        const adjacentPositions = [];
        // Collect surrounding positions including diagonals
        for (let r = row - 1; r <= row + 1; r++) {
            for (let c = col - 1; c <= col + 1; c++) {
                if (r < 0 || c < 0 || c >= cols) continue;
                adjacentPositions.push([r, c]);
            }
        }

        // Track total effect values by type
        const allEffects = {
            safety: 0,
            recreation: 0,
            climate: 0,
            facilities: 0,
            infrastructure: 0
        };

        let activeEventsHtml = '';

        // Loop through all adjacent slots
        adjacentPositions.forEach(([r, c]) => {
            const cell = getCell(r, c);
            if (!cell) return;

            cell.classList.add('bg-green-200');

            // Check for module effects
            const moduleSlot = cell.querySelector('.city-slot[data-module-id]');
            if (moduleSlot) {
                const effects = cell.querySelectorAll('.effect');
                effects.forEach(effect => {
                    const type = effect.dataset.type;
                    const value = parseInt(effect.dataset.value, 10);
                    allEffects[type] += value;
                });
            }

            // Check for active event effects
            const eventSlot = cell.querySelector('.city-slot[data-event-id]');
            if (eventSlot) {
                const eventName = eventSlot.dataset.eventName || 'Actief Event';
                let eventEffectsHtml = '';

                const eventEffects = eventSlot.querySelectorAll('.event-effect');
                eventEffects.forEach(effect => {
                    const type = effect.dataset.type;
                    const value = parseInt(effect.dataset.value, 10);
                    allEffects[type] += value;

                    const typeMap = {
                        safety: 'Veiligheid',
                        recreation: 'Recreatie',
                        climate: 'Milieukwaliteit',
                        facilities: 'Voorzieningen',
                        infrastructure: 'Mobiliteit'
                    };
                    const label = typeMap[type] || type;
                    const color = value > 0 ? 'text-green-600' : (value < 0 ? 'text-red-600' : 'text-gray-600');
                    eventEffectsHtml += `<div class="ml-2 ${color}">${value > 0 ? '+' : ''}${value} ${label}</div>`;
                });

                if (eventEffectsHtml) {
                    activeEventsHtml += `<div class="mt-1 font-bold">${eventName}:</div>${eventEffectsHtml}`;
                }
            }
        });

        // Calculate Quality of Life total
        const qol = Object.values(allEffects).reduce((sum, val) => sum + val, 0);

        const typeMap = {
            safety: 'Veiligheid',
            recreation: 'Recreatie',
            climate: 'Milieukwaliteit',
            facilities: 'Voorzieningen',
            infrastructure: 'Mobiliteit'
        };

        let html = '';

        // Add module + event effect totals per category
        for (const type in allEffects) {
            const val = allEffects[type];
            const label = typeMap[type] || type;
            const color = val > 0 ? 'text-green-600' : (val < 0 ? 'text-red-600' : 'text-gray-600');
            html += `<div class="${color}">${val > 0 ? '+' : ''}${val} ${label}</div>`;
        }

        // Prepend event effects block if any were found
        if (activeEventsHtml) {
            html = `<div class="mb-2 pb-2 border-b border-gray-300">${activeEventsHtml}</div>` + html;
        }

        // Append total QoL
        html += `<div class="mt-1 font-bold ${qol > 0 ? 'text-green-600' : (qol < 0 ? 'text-red-600' : 'text-gray-600')}">
                    Kwaliteit van Leven: ${qol > 0 ? '+' : ''}${qol}
                </div>`;

        // Inject tooltip/overlay content
        const overlay = slot.querySelector('.combined-effects');
        if (overlay) {
            overlay.innerHTML = html;
            overlay.classList.remove('hidden');
        }
    });

    slot.addEventListener('mouseleave', () => {
        // Remove green highlight from all affected cells
        document.querySelectorAll('td.city-cell.bg-green-200').forEach(cell => {
            cell.classList.remove('bg-green-200');
        });

        // Hide the overlay
        const overlay = slot.querySelector('.combined-effects');
        if (overlay) overlay.classList.add('hidden');
    });
});
