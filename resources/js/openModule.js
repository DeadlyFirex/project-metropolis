// public/js/module-modal.js

document.addEventListener('DOMContentLoaded', function () {
    const openBtn = document.getElementById('openModuleForm');
    const closeBtn = document.getElementById('closeModuleForm');
    const modal = document.getElementById('moduleModal');

    // Als de knoppen en modal aanwezig zijn, voeg event listeners toe
    if (openBtn && closeBtn && modal) {
        // Open de modal wanneer de knop wordt geklikt
        openBtn.addEventListener('click', () => modal.classList.remove('hidden'));

        // Sluit de modal wanneer de close knop wordt geklikt
        closeBtn.addEventListener('click', () => modal.classList.add('hidden'));

        // Sluit de modal wanneer er buiten de modal wordt geklikt
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    }
});

