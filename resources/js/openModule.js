if (!window.openModuleInitialized) {
    window.openModuleInitialized = true;

    document.addEventListener('DOMContentLoaded', function () {
        const openBtn = document.getElementById('openModuleForm');
        const closeBtn = document.getElementById('closeModuleForm');
        const modal = document.getElementById('moduleModal');
        const editCloseBtn = document.getElementById('closeEditModuleModal');
        const editModal = document.getElementById('editModuleModal');

        // Open de "toevoegen" modal
        if (openBtn && closeBtn && modal) {
            openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
            closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
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

        // Bulk Delete Functionaliteit
        const selectAllCheckbox = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.module-checkbox');
        const deleteBtn = document.getElementById('deleteSelectedBtn');

        // Verberg de knop standaard
        if (deleteBtn) {
            deleteBtn.style.display = 'none';
        }

        function updateDeleteBtnState() {
            const anyChecked = Array.from(checkboxes).some(chk => chk.checked);

            if (deleteBtn) {
                if (anyChecked) {
                    deleteBtn.style.display = 'inline-block'; // of 'block' als je wil dat ie full width is
                    deleteBtn.disabled = false;
                } else {
                    deleteBtn.style.display = 'none';
                    deleteBtn.disabled = true;
                }
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                checkboxes.forEach(chk => chk.checked = selectAllCheckbox.checked);
                updateDeleteBtnState();
            });
        }

        checkboxes.forEach(chk => {
            chk.addEventListener('change', function () {
                if (!chk.checked) {
                    selectAllCheckbox.checked = false;
                } else if (Array.from(checkboxes).every(c => c.checked)) {
                    selectAllCheckbox.checked = true;
                }
                updateDeleteBtnState();
            });
        });

        updateDeleteBtnState(); // Init state op pagina laden

        if (deleteBtn) {
            deleteBtn.addEventListener('click', function () {
                const selectedIds = Array.from(checkboxes)
                    .filter(chk => chk.checked)
                    .map(chk => chk.value);

                if (selectedIds.length === 0) return;

                if (!confirm(`Weet je zeker dat je deze ${selectedIds.length} modules wilt verwijderen?`)) {
                    return;
                }

                fetch(window.bulkDeleteUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify({ ids: selectedIds })
                })
                    .then(response => {
                        return response.json().then(data => ({
                            ok: response.ok,
                            status: response.status,
                            body: data
                        }));
                    })
                    .then(result => {
                        if (result.ok) {
                            location.reload();
                        } else {
                            console.error('Server response:', result);
                            alert('Er is iets misgegaan bij het verwijderen.');
                        }
                    })
                    .catch(() => alert('Er is iets misgegaan bij het verwijderen.'));
            });
        }
    });

    // Globale functie voor module bewerken
    function openEditModal(moduleId) {
        const module = window.modules.find(module => module.id === moduleId);

        if (!module) {
            alert("Module niet gevonden!");
            return;
        }

        document.getElementById('editName').value = module.name;
        document.getElementById('editDescription').value = module.description;
        document.getElementById('editCategory').value = module.category;

        const form = document.getElementById('editModuleForm');
        form.action = `/modules/${moduleId}`;

        document.getElementById('editModuleModal').classList.remove('hidden');
    }
    window.openEditModal = openEditModal;
}
