document.addEventListener("DOMContentLoaded", () => {
    let selectedModuleId = null;
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
                if (moduleId) {
                    attachModule(moduleId, slot.dataset.slotId);
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

                setTimeout(() => slot.classList.remove('selected'), 500);

                attachModule(selectedModuleId, slot.dataset.slotId);
                selectedModuleId = null;
                moduleCards.forEach(c => c.classList.remove('selected'));
            });
        });
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
        const isMobile = window.innerWidth <= 605; // iets ruimer
        const newMode = isMobile ? 'mobile' : 'desktop';

        if (newMode !== currentMode) {
            clearAllListeners();

            // Wacht even tot DOM is geüpdatet
            setTimeout(() => {
                if (isMobile) {
                    enableMobileMode();
                } else {
                    enableDesktopMode();
                }
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
