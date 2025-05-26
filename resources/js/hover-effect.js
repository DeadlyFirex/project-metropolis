const cols = 4;

function getCell(row, col) {
    return document.querySelector(`td.city-cell[data-row="${row}"][data-col="${col}"]`);
}

document.querySelectorAll('.city-slot').forEach(slot => {
    slot.addEventListener('mouseenter', () => {
        if (!slot.dataset.moduleId) return; // STOP als geen module

        const parentTd = slot.closest('td.city-cell');
        const row = parseInt(parentTd.dataset.row, 10);
        const col = parseInt(parentTd.dataset.col, 10);

        const adjacentPositions = [];
        for (let r = row - 1; r <= row + 1; r++) {
            for (let c = col - 1; c <= col + 1; c++) {
                if (r < 0 || c < 0 || c >= cols) continue;
                adjacentPositions.push([r, c]);
            }
        }

        const allEffects = {};

        adjacentPositions.forEach(([r, c]) => {
            const cell = getCell(r, c);
            if (!cell) return;

            cell.classList.add('bg-green-200');

            const moduleSlot = cell.querySelector('.city-slot[data-module-id]');
            if (!moduleSlot) return;

            const effects = cell.querySelectorAll('.effect');
            effects.forEach(effect => {
                const type = effect.dataset.type;
                const value = parseInt(effect.dataset.value, 10);
                allEffects[type] = (allEffects[type] || 0) + value;
            });
        });

        const qol = Object.values(allEffects).reduce((sum, val) => sum + val, 0);

        const typeMap = {
            safety: 'Veiligheid',
            recreation: 'Recreatie',
            climate: 'Milieukwaliteit',
            facilities: 'Voorzieningen',
            infrastructure: 'Mobiliteit'
        };

        let html = '';
        for (const type in allEffects) {
            const val = allEffects[type];
            const label = typeMap[type] || type;
            const color = val > 0 ? 'text-green-600' : (val < 0 ? 'text-red-600' : 'text-gray-600');
            html += `<div class="${color}">${val > 0 ? '+' : ''}${val} ${label}</div>`;
        }
        html += `<div class="mt-1 font-bold ${qol > 0 ? 'text-green-600' : (qol < 0 ? 'text-red-600' : 'text-gray-600')}">
                        Kwaliteit van Leven: ${qol > 0 ? '+' : ''}${qol}
                    </div>`;

        const overlay = slot.querySelector('.combined-effects');
        if (overlay) {
            overlay.innerHTML = html;
            overlay.classList.remove('hidden');
        }
    });

    slot.addEventListener('mouseleave', () => {
        document.querySelectorAll('td.city-cell.bg-green-200').forEach(cell => {
            cell.classList.remove('bg-green-200');
        });

        const overlay = slot.querySelector('.combined-effects');
        if (overlay) overlay.classList.add('hidden');
    });
});
