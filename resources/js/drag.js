// Wait until the DOM is fully loaded
document.addEventListener("DOMContentLoaded", () => {
    let selectedModuleId = null;
    let selectedSlotId = null;
    let currentMode = null;

    // Show a loading spinner/overlay
    function showLoading() {
        document.getElementById('loading').style.display = 'flex';
    }

    // Hide the loading spinner/overlay
    function hideLoading() {
        document.getElementById('loading').style.display = 'none';
    }

    // Get all module cards and city slots from the DOM
    function getElements() {
        return {
            moduleCards: document.querySelectorAll('.module-card'),
            slots: document.querySelectorAll('.city-slot')
        };
    }

    // Enable drag-and-drop functionality for desktop mode
    function enableDesktopMode() {
        currentMode = 'desktop';

        const slots = document.querySelectorAll('.city-slot');

        // Find modules that are already placed in slots
        const modulesInSlots = Array.from(slots).flatMap(slot => {
            const module = slot.querySelector('[data-module-id]');
            return module ? [module] : [];
        });

        // Get all module cards (library modules)
        const libraryModules = document.querySelectorAll('.module-card');

        // Add draggable support to modules that are already placed in the slots
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

                // Set data for the drag event
                e.dataTransfer.setData('module_id', card.dataset.moduleId);
                e.dataTransfer.setData('name', card.dataset.name || '');
                const img = card.querySelector('img');
                e.dataTransfer.setData('img', img ? img.src : '');
                e.dataTransfer.setData('from_slot_id', moduleSlot ? moduleSlot.dataset.slotId : 'unknown');

                if (img) e.dataTransfer.setDragImage(img, img.width / 2, img.height / 2);
            });
        });

        // Add draggable support to library modules
        libraryModules.forEach(card => {
            card.setAttribute('draggable', 'true');
            card.classList.remove('selected');

            card.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('module_id', card.dataset.moduleId);
                e.dataTransfer.setData('name', card.dataset.name || '');
                const img = card.querySelector('img');
                e.dataTransfer.setData('img', img ? img.src : '');
                e.dataTransfer.setData('from_slot_id', 'library');

                if (img) e.dataTransfer.setDragImage(img, img.width / 2, img.height / 2);
            });
        });

        // Set up drop targets for each slot
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

                const approved = slot.dataset.approved === '1';
                if (approved) {
                    alert("Dit slot is goedgekeurd en kan niet worden gewijzigd.");
                    return;
                }

                if (!moduleId || !toSlotId) return;

                if (fromSlotId === 'library') {
                    // Place module from library into the slot
                    attachModule(moduleId, toSlotId);
                } else if (fromSlotId !== toSlotId) {
                    // Move module from one slot to another
                    moveModule(moduleId, fromSlotId, toSlotId);
                }
            });
        });
    }

    // Enable tap-to-select functionality for mobile mode
    function enableMobileMode() {
        currentMode = 'mobile';
        const { moduleCards, slots } = getElements();

        // Disable drag and handle click for module selection
        moduleCards.forEach(card => {
            card.removeAttribute('draggable');
            card.classList.remove('selected');

            card.addEventListener('click', () => {
                const moduleId = card.dataset.moduleId;
                if (selectedSlotId) {
                    // Attach selected module to the previously selected slot
                    attachModule(moduleId, selectedSlotId);
                    selectedSlotId = null;
                    selectedModuleId = null;
                    clearSelections();
                    removeInlineModulePickers();
                } else {
                    // Select this module
                    selectedModuleId = moduleId;
                    moduleCards.forEach(c => c.classList.remove('selected'));
                    card.classList.add('selected');
                }
            });
        });

        // Handle slot selection
        slots.forEach(slot => {
            slot.classList.remove('selected');
            slot.addEventListener('click', () => {
                const slotId = slot.dataset.slotId;
                if (selectedModuleId) {
                    // Attach selected module to the selected slot
                    attachModule(selectedModuleId, slotId);
                    selectedModuleId = null;
                    selectedSlotId = null;
                    clearSelections();
                    removeInlineModulePickers();
                } else {
                    // Select this slot and show picker
                    selectedSlotId = slotId;
                    clearSelections();
                    slot.classList.add('selected');
                    showModulePicker(slot);
                }
            });
        });
    }

    // Show module picker popup (for mobile)
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

        // Clone and list all available modules
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

            // Attach this module to the selected slot
            clone.addEventListener('click', () => {
                attachModule(clone.dataset.moduleId, slot.dataset.slotId);
                selectedModuleId = null;
                selectedSlotId = null;
                clearSelections();
                removeInlineModulePickers();
            });

            modulePicker.appendChild(clone);
        });

        // Position the picker below the clicked slot
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

    // Remove any open inline module pickers
    function removeInlineModulePickers() {
        document.querySelectorAll('.inline-module-picker').forEach(picker => picker.remove());
    }

    // Clear all selected module cards and slots
    function clearSelections() {
        document.querySelectorAll('.module-card').forEach(c => c.classList.remove('selected'));
        document.querySelectorAll('.city-slot').forEach(s => s.classList.remove('selected'));
    }

    // Remove all event listeners by replacing DOM nodes
    function clearAllListeners() {
        document.querySelectorAll('.module-card').forEach(card => {
            const clone = card.cloneNode(true);
            card.replaceWith(clone);
        });
        document.querySelectorAll('.city-slot').forEach(slot => {
            const clone = slot.cloneNode(true);
            slot.replaceWith(clone);
        });
    }

    // Check screen width and switch between desktop and mobile modes
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

    // Attach a module to a slot (via backend API)
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

    // Move a module from one slot to another (via backend API)
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

    // Initialize view mode and bind to window resize
    handleResizeMode();
    window.addEventListener('resize', () => {
        clearTimeout(window._resizeTimeout);
        window._resizeTimeout = setTimeout(handleResizeMode, 150);
    });
});
