document.addEventListener('DOMContentLoaded', function () {
    const openBtn = document.getElementById('openModuleForm');
    const closeBtn = document.getElementById('closeModuleForm');
    const modal = document.getElementById('moduleModal');
    const editCloseBtn = document.getElementById('closeEditModuleModal');
    const editModal = document.getElementById('editModuleModal');

    // Als de knoppen en modal aanwezig zijn, voeg event listeners toe
    if (openBtn && closeBtn && modal) {
        // Open de "toevoegen" modal
        openBtn.addEventListener('click', () => modal.classList.remove('hidden'));

        // Sluit de "toevoegen" modal
        closeBtn.addEventListener('click', () => modal.classList.add('hidden'));

        // Sluit de "toevoegen" modal als je buiten de modal klikt
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    }

    // Sluit de "bewerk" modal
    if (editCloseBtn && editModal) {
        editCloseBtn.addEventListener('click', () => {
            editModal.classList.add('hidden');
        });
    }
});

function openEditModal(moduleId) {
    const module = window.modules.find(module => module.id === moduleId);

    if (!module) {
        alert("Module niet gevonden!");
        return;
    }

    // Vul het formulier in met de gegevens van de module
    document.getElementById('editName').value = module.name;
    document.getElementById('editDescription').value = module.description;
    document.getElementById('editCategory').value = module.category;

    // Update de form actie URL
    const form = document.getElementById('editModuleForm');
    form.action = `/modules/${moduleId}`;

    // Toon de modal voor bewerken
    document.getElementById('editModuleModal').classList.remove('hidden');
}

// Zorg ervoor dat deze functie beschikbaar is door hem te exporteren als globale functie
window.openEditModal = openEditModal;
