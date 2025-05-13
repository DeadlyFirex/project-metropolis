document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('[data-action="effect-adjust"]').forEach(button => {
        button.addEventListener('click', () => {
            const moduleId = button.dataset.moduleId;
            const type = button.dataset.effectType;
            const delta = parseInt(button.dataset.delta);
            const valueEl = document.querySelector(
                `.effect-value[data-module="${moduleId}"][data-type="${type}"]`
            );

            if (!valueEl) {
                alert('Effect-waarde niet gevonden in de tabel.');
                return;
            }

            const current = parseInt(valueEl.textContent);
            const newValue = Math.max(-5, Math.min(5, current + delta));

            fetch(`/effects/module/${moduleId}/${type}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ value: newValue })
            })
                .then(res => {
                    if (!res.ok) throw new Error('Update mislukt');

                    valueEl.textContent = newValue > 0 ? `+${newValue}` : newValue;
                    valueEl.className = `effect-value font-semibold text-xs ${getEffectColorClass(newValue)}`;
                    //effect controlflash 
                    const flashClass = delta > 0 ? 'effect-flash-up' : 'effect-flash-down';
                    valueEl.classList.add(flashClass);
                    setTimeout(() => valueEl.classList.remove(flashClass), 400);
                    // grid flash
                    document.querySelectorAll(`td[data-slot-id] div[data-module-id="${moduleId}"]`).forEach(el => {
                        const gridCell = el.closest('td');
                        if (gridCell) {
                            gridCell.classList.add(flashClass);
                            setTimeout(() => {
                                gridCell.classList.remove('effect-flash-up', 'effect-flash-down');
                            }, 400);
                        }
                    });



                    updateCalculatedEffectsDisplay(moduleId, type, newValue);
                })
                .catch(err => {
                    alert("Effect kon niet worden aangepast: " + err.message);
                });
        });
    });
});

function updateCalculatedEffectsDisplay(moduleId, type, newValue) {
    const rows = document.querySelectorAll('#calculated-effects-table tbody tr');
    const effectTypes = ['safety', 'recreation', 'climate', 'facilities', 'infrastructure'];
    const totals = {};
    effectTypes.forEach(t => totals[t] = 0);

    rows.forEach(row => {
        const rowModuleId = row.dataset.moduleId;
        const cells = row.querySelectorAll('td');

        effectTypes.forEach((t, index) => {
            const cell = cells[index + 1];
            if (!cell) return;

            let val = parseInt(cell.dataset.value);
            if (rowModuleId === moduleId.toString() && t === type) {
                val = newValue;
                const span = cell.querySelector('.effect-cell');
                if (span) {
                    span.textContent = (val > 0 ? '+' : '') + val;
                    span.className = `effect-cell ${getEffectColorClass(val)}`;
                    cell.dataset.value = val;
                }
            }

            totals[t] += val;
        });
    });

    const totalRow = document.querySelector('#calculated-effects-table tfoot tr');
    if (totalRow) {
        const totalCells = totalRow.querySelectorAll('td');
        effectTypes.forEach((t, i) => {
            const total = totals[t];
            const cell = totalCells[i + 1];
            const span = cell.querySelector('.effect-cell');
            if (span) {
                span.textContent = (total > 0 ? '+' : '') + total;
                span.className = `effect-cell ${getEffectColorClass(total)}`;
                cell.dataset.value = total;
            }
        });
    }
}

function recalculateAllCalculatedEffects() {
    const rows = document.querySelectorAll('#calculated-effects-table tbody tr');
    const effectTypes = ['safety', 'recreation', 'climate', 'facilities', 'infrastructure'];
    const totals = {};
    effectTypes.forEach(t => totals[t] = 0);

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');

        effectTypes.forEach((t, index) => {
            const cell = cells[index + 1];
            if (!cell) return;
            let val = parseInt(cell.dataset.value) || 0;

            const span = cell.querySelector('.effect-cell');
            if (span) {
                span.textContent = (val > 0 ? '+' : '') + val;
                span.className = `effect-cell ${getEffectColorClass(val)}`;
            }

            totals[t] += val;
        });
    });

    const totalRow = document.querySelector('#calculated-effects-table tfoot tr');
    if (totalRow) {
        const totalCells = totalRow.querySelectorAll('td');
        effectTypes.forEach((t, i) => {
            const total = totals[t];
            const cell = totalCells[i + 1];
            const span = cell.querySelector('.effect-cell');
            if (span) {
                span.textContent = (total > 0 ? '+' : '') + total;
                span.className = `effect-cell ${getEffectColorClass(total)}`;
                cell.dataset.value = total;
            }
        });
    }
}

function getEffectColorClass(value) {
    return value > 0
        ? 'text-green-600'
        : value < 0
            ? 'text-red-600'
            : 'text-gray-700';
}
