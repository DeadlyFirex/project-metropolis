document.addEventListener("DOMContentLoaded", () => {
    let selectedModuleId = null;
    let selectedSlotId = null;
    let currentMode = null;

    function getElements() {
        return {
            moduleCards: document.querySelectorAll('.module-card'),
            slots: document.querySelectorAll('.city-slot')
        };
    }

    function enableDesktopMode() {
        currentMode = 'desktop';
        const { moduleCards, slots } = getElements();

        moduleCards.forEach(card => {
            card.setAttribute('draggable', 'true');
            card.classList.remove('selected');

            card.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('module_id', card.dataset.moduleId);
                e.dataTransfer.setData('name', card.dataset.name);
                const img = card.querySelector('img');
                e.dataTransfer.setData('img', img ? img.src : '');
            });
        });

        slots.forEach(slot => {
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
                if (moduleId) attachModule(moduleId, slot.dataset.slotId);
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
        modulePicker.style.position = 'absolute';
        modulePicker.style.backgroundColor = '#fff';
        modulePicker.style.zIndex = '1000';
        modulePicker.style.padding = '8px';
        modulePicker.style.border = '1px solid #ccc';
        modulePicker.style.borderRadius = '6px';
        modulePicker.style.display = 'flex';
        modulePicker.style.flexDirection = 'column';
        modulePicker.style.overflowY = 'auto';
        modulePicker.style.maxHeight = '200px';
        modulePicker.style.gap = '6px';
        modulePicker.style.boxShadow = '0 4px 10px rgba(0,0,0,0.1)';
        modulePicker.style.width = '200px';
        modulePicker.style.minWidth = '200px';
        modulePicker.style.maxWidth = '90vw';

        // Tijdelijk buiten zicht om grootte te meten
        modulePicker.style.left = '-9999px';
        modulePicker.style.top = '-9999px';
        document.body.appendChild(modulePicker);

        document.querySelectorAll('.module-card').forEach(card => {
            const clone = card.cloneNode(true);
            clone.classList.add('inline-option');
            clone.classList.remove('selected');
            clone.style.width = '100%';
            clone.style.height = 'auto';
            clone.style.display = 'flex';
            clone.style.alignItems = 'center';
            clone.style.justifyContent = 'flex-start';
            clone.style.fontSize = '0.9rem';
            clone.style.padding = '0 10px';
            clone.style.textAlign = 'left';
            clone.style.backgroundColor = '#f8f8f8';
            clone.style.border = '1px solid #ddd';
            clone.style.borderRadius = '4px';
            clone.style.cursor = 'pointer';
            clone.style.gap = '10px';

            const img = clone.querySelector('img');
            if (img) {
                img.style.width = '60px';
                img.style.height = '60px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '3px';
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

        // Positie berekenen
        const rect = slot.getBoundingClientRect();
        const pickerRect = modulePicker.getBoundingClientRect();

        let left = rect.left + window.scrollX;
        let top = rect.bottom + window.scrollY + 4;

        // Check overflow rechts
        const overflowRight = (left + pickerRect.width) - window.innerWidth;
        if (overflowRight > 0) {
            left = Math.max(10 + window.scrollX, left - overflowRight - 10);
        }

        // Check overflow links (voor het geval)
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

    handleResizeMode();

    window.addEventListener('resize', () => {
        clearTimeout(window._resizeTimeout);
        window._resizeTimeout = setTimeout(handleResizeMode, 150);
    });

    function attachModule(moduleId, slotId) {
        fetch('/simulatie/koppel-module', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ module_id: moduleId, slot_id: slotId })
        })
            .then(response => {
                if (response.status === 422) alert("Je mag deze categorieën niet naast elkaar hebben.");
                else if (response.status === 409) alert("Je mag niet meer van deze categorie neerzetten.");
                else if (response.ok) location.reload();
                else alert("Er is iets misgegaan bij het koppelen.");
            })
            .catch(() => alert("Er is iets misgegaan bij het koppelen."));
    }
});
