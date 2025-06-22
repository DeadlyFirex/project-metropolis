import { attachModule, moveModule } from '../utils/api.js';
import {
    showLoading, hideLoading,
    clearSelections, clearAllListeners,
    removeInlineModulePickers
} from '../utils/dom.js';

let selectedModuleId = null;
let selectedSlotId = null;
let currentMode = null;

export function initModuleDragAndDrop() {
    handleResizeMode();
    window.addEventListener('resize', debounce(handleResizeMode, 150));
}

function debounce(fn, delay) {
    let timeout;
    return () => {
        clearTimeout(timeout);
        timeout = setTimeout(fn, delay);
    };
}

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
        card.addEventListener('dragstart', e => {
            e.dataTransfer.setData('module_id', card.dataset.moduleId);
            e.dataTransfer.setData('name', card.dataset.name || '');
            e.dataTransfer.setData('img', card.querySelector('img')?.src || '');
            e.dataTransfer.setData('from_slot_id', 'library');
            if (card.querySelector('img')) {
                e.dataTransfer.setDragImage(card.querySelector('img'), 30, 30);
            }
        });
    });

    slots.forEach(slot => {
        slot.classList.remove('selected');

        // Enable drag from placed modules
        const card = slot.querySelector('[data-module-id]');
        if (card) {
            card.setAttribute('draggable', 'true');
            card.classList.remove('selected');

            card.addEventListener('dragstart', e => {
                if (slot.dataset.approved === '1') {
                    alert("Dit slot is goedgekeurd, je kunt de module niet verplaatsen.");
                    e.preventDefault();
                    return;
                }

                e.dataTransfer.setData('module_id', card.dataset.moduleId);
                e.dataTransfer.setData('name', card.dataset.name || '');
                e.dataTransfer.setData('img', card.querySelector('img')?.src || '');
                e.dataTransfer.setData('from_slot_id', slot.dataset.slotId);
                if (card.querySelector('img')) {
                    e.dataTransfer.setDragImage(card.querySelector('img'), 30, 30);
                }
            });
        }

        // Setup drop area
        slot.addEventListener('dragover', e => {
            e.preventDefault();
            slot.classList.add('drag-over');
        });

        slot.addEventListener('dragleave', () => {
            slot.classList.remove('drag-over');
        });

        slot.addEventListener('drop', e => {
            e.preventDefault();
            slot.classList.remove('drag-over');

            const moduleId = e.dataTransfer.getData('module_id');
            const fromSlotId = e.dataTransfer.getData('from_slot_id');
            const toSlotId = slot.dataset.slotId;

            if (slot.dataset.approved === '1') {
                alert("Dit slot is goedgekeurd en kan niet worden gewijzigd.");
                return;
            }

            if (!moduleId || !toSlotId) return;

            if (fromSlotId === 'library') {
                attachModule(moduleId, toSlotId);
            } else if (fromSlotId !== toSlotId) {
                moveModule(moduleId, fromSlotId, toSlotId);
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
            if (selectedSlotId) {
                attachModule(card.dataset.moduleId, selectedSlotId);
                resetSelection();
            } else {
                selectedModuleId = card.dataset.moduleId;
                clearSelections();
                card.classList.add('selected');
            }
        });
    });

    slots.forEach(slot => {
        slot.classList.remove('selected');
        slot.addEventListener('click', () => {
            if (selectedModuleId) {
                attachModule(selectedModuleId, slot.dataset.slotId);
                resetSelection();
            } else {
                selectedSlotId = slot.dataset.slotId;
                clearSelections();
                slot.classList.add('selected');
                showModulePicker(slot);
            }
        });
    });
}

function showModulePicker(slot) {
    removeInlineModulePickers();
    const picker = document.createElement('div');
    picker.className = 'inline-module-picker';

    Object.assign(picker.style, {
        position: 'absolute',
        backgroundColor: '#fff',
        zIndex: '1000',
        padding: '8px',
        border: '1px solid #ccc',
        borderRadius: '6px',
        display: 'flex',
        flexDirection: 'column',
        maxHeight: '200px',
        overflowY: 'auto',
        boxShadow: '0 4px 10px rgba(0,0,0,0.1)',
        width: '200px',
    });

    document.querySelectorAll('.module-card').forEach(card => {
        const clone = card.cloneNode(true);
        clone.classList.add('inline-option');
        clone.classList.remove('selected');
        clone.style.cursor = 'pointer';

        clone.addEventListener('click', () => {
            attachModule(clone.dataset.moduleId, slot.dataset.slotId);
            resetSelection();
        });

        picker.appendChild(clone);
    });

    document.body.appendChild(picker);

    const rect = slot.getBoundingClientRect();
    picker.style.top = `${rect.bottom + window.scrollY + 4}px`;
    picker.style.left = `${Math.min(rect.left + window.scrollX, window.innerWidth - 220)}px`;
}

function handleResizeMode() {
    const isMobile = window.innerWidth <= 605;
    const newMode = isMobile ? 'mobile' : 'desktop';

    if (newMode !== currentMode) {
        clearAllListeners();
        removeInlineModulePickers();
        setTimeout(() => {
            isMobile ? enableMobileMode() : enableDesktopMode();
        }, 50);
    }
}

function resetSelection() {
    selectedSlotId = null;
    selectedModuleId = null;
    clearSelections();
    removeInlineModulePickers();
}
