// Prevent the script from running multiple times
if (!window.openModuleInitialized) {
    window.openModuleInitialized = true;

    // Wait for the DOM to fully load before accessing elements
    document.addEventListener('DOMContentLoaded', function () {
        // Get DOM elements for the modals and their buttons
        const openBtn = document.getElementById('openModuleForm');
        const closeBtn = document.getElementById('closeModuleForm');
        const modal = document.getElementById('moduleModal');
        const editCloseBtn = document.getElementById('closeEditModuleModal');
        const editModal = document.getElementById('editModuleModal');

        // Open and close functionality for the "add module" modal
        if (openBtn && closeBtn && modal) {
            openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
            closeBtn.addEventListener('click', () => modal.classList.add('hidden'));

            // Close modal when clicking outside of it
            window.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        }

        // Close functionality for the "edit module" modal
        if (editCloseBtn && editModal) {
            editCloseBtn.addEventListener('click', () => {
                editModal.classList.add('hidden');
            });
        }

        // Bulk delete logic
        const selectAllCheckbox = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.module-checkbox');
        const deleteBtn = document.getElementById('deleteSelectedBtn');

        // Hide the delete button initially
        if (deleteBtn) {
            deleteBtn.style.display = 'none';
        }

        // Enable or disable the delete button based on checkbox state
        function updateDeleteBtnState() {
            const anyChecked = Array.from(checkboxes).some(chk => chk.checked);

            if (deleteBtn) {
                if (anyChecked) {
                    deleteBtn.style.display = 'inline-block'; // use 'block' for full width
                    deleteBtn.disabled = false;
                } else {
                    deleteBtn.style.display = 'none';
                    deleteBtn.disabled = true;
                }
            }
        }

        // "Select All" checkbox toggles all module checkboxes
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                checkboxes.forEach(chk => chk.checked = selectAllCheckbox.checked);
                updateDeleteBtnState();
            });
        }

        // Update state when any checkbox is toggled
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

        // Initialize delete button state on page load
        updateDeleteBtnState();

        // Handle bulk delete button click
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function () {
                const selectedIds = Array.from(checkboxes)
                    .filter(chk => chk.checked)
                    .map(chk => chk.value);

                if (selectedIds.length === 0) return;

                if (!confirm(`Weet je zeker dat je deze ${selectedIds.length} modules wilt verwijderen?`)) {
                    return;
                }

                // Send delete request to server using Fetch API
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
                            // Reload the page to reflect the changes
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

    // Global function to open and populate the "edit module" modal
    function openEditModal(moduleId) {
        const module = window.modules.find(module => module.id === moduleId);

        if (!module) {
            alert("Module niet gevonden!");
            return;
        }

        // Populate form fields with module data
        document.getElementById('editName').value = module.name;
        document.getElementById('editDescription').value = module.description;
        document.getElementById('editCategory').value = module.category;

        // Update the form action with the module ID
        const form = document.getElementById('editModuleForm');
        form.action = `/modules/${moduleId}`;

        // Show the modal
        document.getElementById('editModuleModal').classList.remove('hidden');
    }
    // Make the edit function available globally
    window.openEditModal = openEditModal;
}
