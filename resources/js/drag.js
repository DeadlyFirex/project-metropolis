document.addEventListener("DOMContentLoaded", () => {
    let selectedModuleId = null;
    let selectedSlotId = null;
    let currentMode = null;

    function showLoading() {
        document.getElementById('loading').style.display = 'flex';
    }

    function hideLoading() {
        document.getElementById('loading').style.display = 'none';
    }

    function getElements() {
        return {
            moduleCards: document.querySelectorAll('.module-card'),
            slots: document.querySelectorAll('.city-slot')
        };
    }

    function enableDesktopMode() {
        currentMode = 'desktop';

        const slots = document.querySelectorAll('.city-slot');

        // Modules in slots (already placed)
        const modulesInSlots = Array.from(slots).flatMap(slot => {
            const module = slot.querySelector('[data-module-id]');
            return module ? [module] : [];
        });

        // Library modules zijn gewoon alle .module-card buiten de grid (of ook in grid? Pas aan indien nodig)
        const libraryModules = document.querySelectorAll('.module-card');

        // Voeg draggable en dragstart toe aan modules in slots
        modulesInSlots.forEach(card => {
            card.setAttribute('draggable', 'true');
            card.classList.remove('selected');

            card.addEventListener('dragstart', (e) => {
                const moduleSlot = card.closest('.city-slot');
                if (moduleSlot && moduleSlot.dataset.approved === '1') {
                    alert("Dit slot is goedgekeurd, je kunt de module niet verplaatsen.");
                    e.preventDefault();
                    return;
                }

                console.log('Dragstart from slot module:', card.dataset.moduleId);
                e.dataTransfer.setData('module_id', card.dataset.moduleId);
                e.dataTransfer.setData('name', card.dataset.name || '');
                const img = card.querySelector('img');
                e.dataTransfer.setData('img', img ? img.src : '');
                e.dataTransfer.setData('from_slot_id', moduleSlot ? moduleSlot.dataset.slotId : 'unknown');

                if (img) e.dataTransfer.setDragImage(img, img.width / 2, img.height / 2);
            });
        });

        // Voeg draggable en dragstart toe aan library modules (ook die in de grid, voorzichtig als dat zo is)
        libraryModules.forEach(card => {
            card.setAttribute('draggable', 'true');
            card.classList.remove('selected');

            card.addEventListener('dragstart', (e) => {
                console.log('Dragstart from library module:', card.dataset.moduleId);
                e.dataTransfer.setData('module_id', card.dataset.moduleId);
                e.dataTransfer.setData('name', card.dataset.name || '');
                const img = card.querySelector('img');
                e.dataTransfer.setData('img', img ? img.src : '');
                e.dataTransfer.setData('from_slot_id', 'library');

                if (img) e.dataTransfer.setDragImage(img, img.width / 2, img.height / 2);
            });
        });

        // Setup drop events op slots
        slots.forEach(slot => {
            slot.classList.remove('selected');

            slot.addEventListener('dragover', (e) => {
                e.preventDefault();
                slot.classList.add('drag-over');
            });

            slot.addEventListener('dragleave', () => {
                slot.classList.remove('drag-over');
            });

            slot.addEventListener('drop', (e) => {
                e.preventDefault();
                slot.classList.remove('drag-over');

                const moduleId = e.dataTransfer.getData('module_id');
                const fromSlotId = e.dataTransfer.getData('from_slot_id');
                const toSlotId = slot.dataset.slotId;

                console.log(`Drop event: moduleId=${moduleId}, fromSlotId=${fromSlotId}, toSlotId=${toSlotId}`);

                const approved = slot.dataset.approved === '1';
                if (approved) {
                    alert("Dit slot is goedgekeurd en kan niet worden gewijzigd.");
                    return;
                }

                if (!moduleId || !toSlotId) {
                    console.warn('Missing moduleId or toSlotId, ignoring drop');
                    return;
                }

                if (fromSlotId === 'library') {
                    // Vanuit library naar grid
                    attachModule(moduleId, toSlotId);
                } else if (fromSlotId !== toSlotId) {
                    // Verplaatsen binnen grid
                    moveModule(moduleId, fromSlotId, toSlotId);
                } else {
                    // Zelfde slot, niets doen
                    console.log('Dropped in same slot, ignoring');
                }
            });
        });
    }

    function enableMobileMode() {
        currentMode = 'mobile';
        const { moduleCards, slots } = getElements();

        moduleCards.forEach(card => {
            card.removeAttribute('draggable');
            card.classList.remove('selected');

            card.addEventListener('click', () => {
                const moduleId = card.dataset.moduleId;
                if (selectedSlotId) {
                    attachModule(moduleId, selectedSlotId);
                    selectedSlotId = null;
                    selectedModuleId = null;
                    clearSelections();
                    removeInlineModulePickers();
                } else {
                    selectedModuleId = moduleId;
                    moduleCards.forEach(c => c.classList.remove('selected'));
                    card.classList.add('selected');
                }
            });
        });

        slots.forEach(slot => {
            slot.classList.remove('selected');
            slot.addEventListener('click', () => {
                const slotId = slot.dataset.slotId;
                if (selectedModuleId) {
                    attachModule(selectedModuleId, slotId);
                    selectedModuleId = null;
                    selectedSlotId = null;
                    clearSelections();
                    removeInlineModulePickers();
                } else {
                    selectedSlotId = slotId;
                    clearSelections();
                    slot.classList.add('selected');
                    showModulePicker(slot);
                }
            });
        });
    }

    function showModulePicker(slot) {
        removeInlineModulePickers();

        const modulePicker = document.createElement('div');
        modulePicker.classList.add('inline-module-picker');
        Object.assign(modulePicker.style, {
            position: 'absolute',
            backgroundColor: '#fff',
            zIndex: '1000',
            padding: '8px',
            border: '1px solid #ccc',
            borderRadius: '6px',
            display: 'flex',
            flexDirection: 'column',
            overflowY: 'auto',
            maxHeight: '200px',
            gap: '6px',
            boxShadow: '0 4px 10px rgba(0,0,0,0.1)',
            width: '200px',
            minWidth: '200px',
            maxWidth: '90vw',
            left: '-9999px',
            top: '-9999px'
        });
        document.body.appendChild(modulePicker);

        document.querySelectorAll('.module-card').forEach(card => {
            const clone = card.cloneNode(true);
            clone.classList.add('inline-option');
            clone.classList.remove('selected');
            Object.assign(clone.style, {
                width: '100%',
                height: 'auto',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'flex-start',
                fontSize: '0.9rem',
                padding: '0 10px',
                textAlign: 'left',
                backgroundColor: '#f8f8f8',
                border: '1px solid #ddd',
                borderRadius: '4px',
                cursor: 'pointer',
                gap: '10px'
            });

            const img = clone.querySelector('img');
            if (img) {
                Object.assign(img.style, {
                    width: '60px',
                    height: '60px',
                    objectFit: 'cover',
                    borderRadius: '3px'
                });
            }

            clone.addEventListener('click', () => {
                attachModule(clone.dataset.moduleId, slot.dataset.slotId);
                selectedModuleId = null;
                selectedSlotId = null;
                clearSelections();
                removeInlineModulePickers();
            });

            modulePicker.appendChild(clone);
        });

        const rect = slot.getBoundingClientRect();
        const pickerRect = modulePicker.getBoundingClientRect();

        let left = rect.left + window.scrollX;
        let top = rect.bottom + window.scrollY + 4;

        const overflowRight = (left + pickerRect.width) - window.innerWidth;
        if (overflowRight > 0) {
            left = Math.max(10 + window.scrollX, left - overflowRight - 10);
        }

        if (left < 10 + window.scrollX) {
            left = 10 + window.scrollX;
        }

        modulePicker.style.left = `${left}px`;
        modulePicker.style.top = `${top}px`;
    }

    function removeInlineModulePickers() {
        document.querySelectorAll('.inline-module-picker').forEach(picker => picker.remove());
    }

    function clearSelections() {
        document.querySelectorAll('.module-card').forEach(c => c.classList.remove('selected'));
        document.querySelectorAll('.city-slot').forEach(s => s.classList.remove('selected'));
    }

    function clearAllListeners() {
        // Clone nodes om listeners te verwijderen
        document.querySelectorAll('.module-card').forEach(card => {
            const clone = card.cloneNode(true);
            card.replaceWith(clone);
        });
        document.querySelectorAll('.city-slot').forEach(slot => {
            const clone = slot.cloneNode(true);
            slot.replaceWith(clone);
        });
    }

    function handleResizeMode() {
        const isMobile = window.innerWidth <= 605;
        const newMode = isMobile ? 'mobile' : 'desktop';

        if (newMode !== currentMode) {
            clearAllListeners();
            removeInlineModulePickers();
            setTimeout(() => {
                if (isMobile) enableMobileMode();
                else enableDesktopMode();
            }, 50);
        }
    }

    function attachModule(moduleId, slotId) {
        showLoading();

        fetch('/simulatie/koppel-module', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ module_id: moduleId, slot_id: slotId })
        })
            .then(response => {
                hideLoading();
                if (response.status === 422) alert("Je mag deze categorieën niet naast elkaar hebben.");
                else if (response.status === 409) alert("Je mag niet meer van deze categorie neerzetten.");
                else if (response.ok) location.reload();
                else alert("Er is iets misgegaan bij het koppelen.");
            })
            .catch(() => {
                hideLoading();
                alert("Er is iets misgegaan bij het koppelen.");
            });
    }

    function moveModule(moduleId, fromSlotId, toSlotId) {
        showLoading();

        fetch('/simulatie/verplaats-module', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                module_id: moduleId,
                from_slot_id: fromSlotId,
                to_slot_id: toSlotId
            })
        })
            .then(response => {
                hideLoading();
                if (response.ok) location.reload();
                else alert("Er is iets misgegaan bij het verplaatsen.");
            })
            .catch(() => {
                hideLoading();
                alert("Er is iets misgegaan bij het verplaatsen.");
            });
    }

    // Init
    handleResizeMode();
    window.addEventListener('resize', () => {
        clearTimeout(window._resizeTimeout);
        window._resizeTimeout = setTimeout(handleResizeMode, 150);
    });
});
