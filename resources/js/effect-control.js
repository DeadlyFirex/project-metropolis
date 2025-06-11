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

                    // Update the individual module effect cell in the 'Effecten beheren' view
                    valueEl.textContent = newValue > 0 ? `+${newValue}` : newValue;
                    valueEl.className = `effect-value font-semibold text-xs ${getEffectColorClass(newValue)}`;

                    // Update the city grid module effects
                    document.querySelectorAll(`td[data-slot-id] .city-slot[data-module-id="${moduleId}"] .effect[data-type="${type}"]`).forEach(effectEl => {
                        effectEl.dataset.value = newValue;
                        effectEl.textContent = (newValue > 0 ? '+' : '') + newValue + ' ' + getEffectLabel(type);
                        effectEl.className = `effect ${getEffectColorClass(newValue)}`;
                    });

                    // Effect flash for the 'Effecten beheren' view
                    const flashClass = delta > 0 ? 'effect-flash-up' : 'effect-flash-down';
                    valueEl.classList.add(flashClass);
                    setTimeout(() => valueEl.classList.remove(flashClass), 400);

                    // Grid flash for the city grid
                    document.querySelectorAll(`td[data-slot-id] div[data-module-id="${moduleId}"]`).forEach(el => {
                        const gridCell = el.closest('td');
                        if (gridCell) {
                            gridCell.classList.add(flashClass);
                            setTimeout(() => {
                                gridCell.classList.remove('effect-flash-up', 'effect-flash-down');
                            }, 400);
                        }
                    });

                    // Recalculate all totals in the 'Effecten op de grid' table
                    recalculateAllCalculatedEffects();
                    // Update grid module effects to refresh tooltips
                    updateGridModuleEffects(moduleId);
                })
                .catch(err => {
                    alert("Effect kon niet worden aangepast: " + err.message);
                });
        });
    });
});

function recalculateAllCalculatedEffects() {
    const effectTypes = ['safety', 'recreation', 'climate', 'facilities', 'infrastructure'];

    // Get all unique modules and their current effect values from the effect control view
    const moduleData = {};
    const moduleNames = {}; // To store moduleId -> moduleName mapping

    document.querySelectorAll('.effect-value[data-module]').forEach(el => {
        const moduleId = el.dataset.module;
        const type = el.dataset.type;
        const value = parseInt(el.textContent) || 0;

        if (!moduleData[moduleId]) {
            moduleData[moduleId] = {};
        }
        moduleData[moduleId][type] = value;

        // Get module name from the same row
        const row = el.closest('tr');
        if (row) {
            const nameCell = row.querySelector('td:first-child');
            if (nameCell && nameCell.textContent) {
                moduleNames[moduleId] = nameCell.textContent.trim();
            }
        }
    });

    // Update individual module rows in calculated effects table
    Object.keys(moduleData).forEach(moduleId => {
        const moduleName = moduleNames[moduleId];
        if (!moduleName) return;

        // Find the corresponding row in the calculated effects table by module name
        document.querySelectorAll('#calculated-effects-table tfoot tr').forEach(row => {
            const moduleCell = row.querySelector('td:first-child');
            if (moduleCell && moduleCell.textContent === `Module: ${moduleName}`) {
                // Update each effect type cell in this row
                let rowQol = 0;
                effectTypes.forEach((type, index) => {
                    const cell = row.children[index + 1]; // +1 to skip first column
                    if (cell && moduleData[moduleId][type] !== undefined) {
                        const value = moduleData[moduleId][type];
                        const span = cell.querySelector('span');
                        if (span) {
                            span.textContent = (value > 0 ? '+' : '') + value;
                            span.className = `${value < 0 ? 'text-red-600' : (value > 0 ? 'text-green-600' : 'text-gray-800')}`;
                        }
                        rowQol += value;
                    }
                });

                // Update QOL cell for this module
                const qolCell = row.querySelector('td:last-child');
                if (qolCell) {
                    qolCell.textContent = (rowQol > 0 ? '+' : '') + rowQol;
                    qolCell.className = `px-1 py-1 border border-gray-300 ${rowQol < 0 ? 'text-red-600' : (rowQol > 0 ? 'text-green-600' : 'text-gray-800')}`;
                }
            }
        });
    });

    // Recalculate totals for the combined total row
    const totals = {
        modules: {},
        primaryEvents: {},
        adjacentEvents: {}
    };

    // Initialize totals
    effectTypes.forEach(type => {
        totals.modules[type] = 0;
        totals.primaryEvents[type] = 0;
        totals.adjacentEvents[type] = 0;
    });

    // Calculate module totals from updated values
    Object.keys(moduleData).forEach(moduleId => {
        effectTypes.forEach(type => {
            if (moduleData[moduleId][type] !== undefined) {
                totals.modules[type] += moduleData[moduleId][type];
            }
        });
    });

    // Get event totals from existing calculated effects (these don't change when modules change)
    document.querySelectorAll('#calculated-effects-table tfoot tr').forEach(row => {
        const firstCell = row.querySelector('td:first-child');
        if (firstCell) {
            if (firstCell.textContent === 'Primaire Effecten') {
                effectTypes.forEach((type, index) => {
                    const cell = row.children[index + 1];
                    if (cell) {
                        const span = cell.querySelector('span');
                        if (span) {
                            const value = parseFloat(span.textContent.replace('+', '')) || 0;
                            totals.primaryEvents[type] = value;
                        }
                    }
                });
            } else if (firstCell.textContent === 'Aangrenzende Effecten') {
                effectTypes.forEach((type, index) => {
                    const cell = row.children[index + 1];
                    if (cell) {
                        const span = cell.querySelector('span');
                        if (span) {
                            const value = parseFloat(span.textContent.replace('+', '')) || 0;
                            totals.adjacentEvents[type] = value;
                        }
                    }
                });
            }
        }
    });

    // Update the "Gecombineerd Totaal" row
    const combinedTotalRow = document.querySelector('#calculated-effects-table tfoot tr.bg-gray-300');
    if (combinedTotalRow) {
        let combinedTotalQol = 0;
        effectTypes.forEach((type, index) => {
            const combinedTotal = totals.modules[type] + totals.primaryEvents[type] + totals.adjacentEvents[type];
            combinedTotalQol += combinedTotal;

            const cell = combinedTotalRow.children[index + 1]; // +1 to skip first column
            if (cell) {
                const span = cell.querySelector('.effect-cell');
                if (span) {
                    span.textContent = (combinedTotal > 0 ? '+' : '') + Math.round(combinedTotal * 10) / 10;
                    span.className = `effect-cell ${combinedTotal < 0 ? 'text-red-600' : (combinedTotal > 0 ? 'text-green-600' : 'text-gray-800')}`;
                }
            }
        });

        const qolCell = combinedTotalRow.querySelector('td:last-child');
        if (qolCell) {
            qolCell.textContent = (combinedTotalQol > 0 ? '+' : '') + Math.round(combinedTotalQol * 10) / 10;
            qolCell.className = `px-1 py-1 border border-gray-300 font-semibold ${combinedTotalQol < 0 ? 'text-red-600' : (combinedTotalQol > 0 ? 'text-green-600' : 'text-gray-800')}`;
        }
    }
}

function getEffectColorClass(value) {
    return value > 0
        ? 'text-green-600'
        : value < 0
            ? 'text-red-600'
            : 'text-gray-700';
}

function updateGridModuleEffects(moduleId) {
    fetch(`/api/modules/${moduleId}/effects`)
        .then(res => res.json())
        .then(data => {
            // Update all module instances in the grid
            document.querySelectorAll(`.city-slot[data-module-id="${moduleId}"]`).forEach(slot => {
                const effectsContainer = slot.querySelector('.grid-effects');
                if (effectsContainer) {
                    effectsContainer.innerHTML = '';
                    data.effects.forEach(effect => {
                        if (effect.value !== 0) {
                            const div = document.createElement('div');
                            div.className = 'effect';
                            div.dataset.type = effect.type;
                            div.dataset.value = effect.value;
                            div.textContent = `${effect.value > 0 ? '+' : ''}${effect.value} ${getEffectLabel(effect.type)}`;
                            div.classList.add(getEffectColorClass(effect.value));
                            effectsContainer.appendChild(div);
                        }
                    });
                    if (data.effects.length > 0) {
                        effectsContainer.classList.remove('hidden');
                    } else {
                        effectsContainer.classList.add('hidden');
                    }
                }
            });

            // Clear combined effects tooltips so they refresh with new data
            document.querySelectorAll(`.city-slot[data-module-id="${moduleId}"] .combined-effects`).forEach(tooltip => {
                tooltip.innerHTML = '';
            });
        })
        .catch(err => {
            console.error('Failed to update grid module effects:', err);
        });
}

function getEffectLabel(type) {
    const typeMap = {
        'safety': 'Veiligheid',
        'recreation': 'Recreatie',
        'climate': 'Milieukwaliteit',
        'facilities': 'Voorzieningen',
        'infrastructure': 'Mobiliteit'
    };
    return typeMap[type] || type;
}
