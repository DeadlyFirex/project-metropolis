document.addEventListener("DOMContentLoaded", () => {
    const functions = document.querySelectorAll('.function');
    const modules = document.querySelectorAll('img[draggable="true"][data-module-id]');
    const slots = document.querySelectorAll('.city-slot');

    functions.forEach(func => {
        func.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('type', 'function');
            e.dataTransfer.setData('function', func.dataset.function);
        });
    });

    modules.forEach(mod => {
        mod.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('type', 'module');
            e.dataTransfer.setData('module_id', mod.dataset.moduleId);
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

            const type = e.dataTransfer.getData('type');
            const slotId = slot.dataset.slotId;

            if (type === 'function') {
                const func = e.dataTransfer.getData('function');
                slot.innerHTML = `<img src="/images/${func}.png" alt="${func}" class="assigned w-[60px]">`;
                slot.classList.remove('bg-gray-100');
                slot.classList.add('bg-red-200');
            }

            if (type === 'module') {
                const moduleId = e.dataTransfer.getData('module_id');

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
                .then(() => {
                    location.reload();
                })
                .catch(() => {
                    alert("Er is iets misgegaan bij het koppelen.");
                });
            }
        });
    });
});
