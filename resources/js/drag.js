document.addEventListener("DOMContentLoaded", () => {
    const functions = document.querySelectorAll('.function');
    const slots = document.querySelectorAll('.city-slot');

    functions.forEach(func => {
        func.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('function', func.dataset.function);
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
            const func = e.dataTransfer.getData('function');
            slot.innerHTML = `<div class="text-sm text-center">${func}</div>`;
        });
    });
});

console.log('✅ drag.js loaded');
