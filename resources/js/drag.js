document.addEventListener("DOMContentLoaded", () => {
    let selectedModuleId = null;
    let currentMode = null; // 'mobile' of 'desktop'

    const getElements = () => {
        return {
            moduleCards: document.querySelectorAll('.module-card[draggable="true"]'),
            slots: document.querySelectorAll('.city-slot')
        };
    };

    function enableDesktopMode() {
        const { moduleCards, slots } = getElements();
        currentMode = 'desktop';

        moduleCards.forEach(card => {
            card.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('type', 'module');
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
                const slotId = slot.dataset.slotId;
                if (moduleId) {
                    attachModule(moduleId, slotId);
                }
            });
        });
    }

    function enableMobileMode() {
        const { moduleCards, slots } = getElements();
        currentMode = 'mobile';

        moduleCards.forEach(card => {
            card.addEventListener('click', () => {
                moduleCards.forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                selectedModuleId = card.dataset.moduleId;
            });
        });

        slots.forEach(slot => {
            slot.addEventListener('click', () => {
                if (!selectedModuleId) return;

                slots.forEach(s => s.classList.remove('selected'));
                slot.classList.add('selected');

                setTimeout(() => {
                    slot.classList.remove('selected');
                }, 500);

                attachModule(selectedModuleId, slot.dataset.slotId);
                selectedModuleId = null;
                moduleCards.forEach(c => c.classList.remove('selected'));
            });
        });
    }

    function clearAllListeners() {
        const { moduleCards, slots } = getElements();

        moduleCards.forEach(card => {
            const clone = card.cloneNode(true);
            card.replaceWith(clone);
        });

        slots.forEach(slot => {
            const clone = slot.cloneNode(true);
            slot.replaceWith(clone);
        });
    }

    function handleResizeMode() {
        const isMobileNow = window.innerWidth <= 600;
        const newMode = isMobileNow ? 'mobile' : 'desktop';

        if (newMode !== currentMode) {
            clearAllListeners();

            if (newMode === 'mobile') {
                enableMobileMode();
            } else {
                enableDesktopMode();
            }
        }
    }

    window.addEventListener('resize', () => {
        clearTimeout(window._resizeTimeout);
        window._resizeTimeout = setTimeout(handleResizeMode, 150);
    });

    // Start initial mode
    handleResizeMode();

    function attachModule(moduleId, slotId) {
        fetch('/simulatie/koppel-module', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                module_id: moduleId,
                slot_id: slotId
            })
        })
        .then(response => {
            if (response.status === 422) {
                alert("Je mag deze categorieën niet naast elkaar hebben.");
            } else if (response.status === 409) {
                alert("Je mag niet meer van deze categorie neerzetten.");
            } else if (response.ok) {
                location.reload();
            } else {
                alert("Er is iets misgegaan bij het koppelen.");
            }
        })
        .catch(() => {
            alert("Er is iets misgegaan bij het koppelen.");
        });
    }
});
