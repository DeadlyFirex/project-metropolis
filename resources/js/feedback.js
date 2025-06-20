/**
 * Sets up everything for the feedback panel:
 * - Opening/closing the sidebar
 * - Sending new feedback with fetch
 * - Re-loading the feedback list without page refresh
 * - Making sure edit/delete buttons keep working
 */
export function initFeedback() {
    const openBtn = document.getElementById('open-feedback');
    const closeBtn = document.getElementById('close-feedback');
    const panel = document.getElementById('feedback-panel');
    const feedbackForm = document.getElementById('feedback-form');
    const feedbackIndexUrl = document.querySelector('meta[name="feedback-index-url"]')?.content;

    // Open and close the sidebar
    if (openBtn && closeBtn && panel) {
        openBtn.addEventListener('click', () => {
            panel.classList.remove('translate-x-full');
        });

        closeBtn.addEventListener('click', () => {
            panel.classList.add('translate-x-full');
        });
    }

    // Send new feedback without refreshing the page
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const textarea = feedbackForm.querySelector('textarea[name="content"]');
            const content = textarea.value.trim();
            const url = feedbackForm.dataset.url;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            if (!content) return;

            try {
                await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({ content })
                });

                textarea.value = '';
                loadFeedbackList(feedbackIndexUrl); // Update the list
            } catch (error) {
                console.error('Something went wrong while sending feedback:', error);
            }
        });
    }

    bindInlineEditEvents(feedbackIndexUrl);
}

/**
 * Makes the edit, cancel, save and delete buttons work.
 * This is called every time the list is updated.
 */
function bindInlineEditEvents(feedbackIndexUrl) {
    // Edit button
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.dataset.editId;
            const wrapper = document.querySelector(`div[data-id="${id}"]`);
            wrapper.querySelector('.view-mode')?.classList.add('hidden');
            wrapper.querySelector('.feedback-edit-form')?.classList.remove('hidden');
        });
    });

    // Cancel button
    document.querySelectorAll('.cancel-edit-btn').forEach(button => {
        button.addEventListener('click', () => {
            const wrapper = button.closest('div[data-id]');
            wrapper.querySelector('.view-mode')?.classList.remove('hidden');
            wrapper.querySelector('.feedback-edit-form')?.classList.add('hidden');
        });
    });

    // Save (update) feedback
    document.querySelectorAll('.feedback-edit-form').forEach(form => {
        form.addEventListener('submit', async e => {
            e.preventDefault();
            const content = form.querySelector('textarea').value.trim();
            const url = form.action;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            if (!content) return;

            try {
                await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({ content })
                });

                loadFeedbackList(feedbackIndexUrl);
            } catch (err) {
                console.error('Could not update feedback:', err);
            }
        });
    });

    // Delete feedback
    document.querySelectorAll('.feedback-delete-form').forEach(form => {
        const deleteBtn = form.querySelector('.delete-btn');
        if (!deleteBtn) return;

        deleteBtn.addEventListener('click', async e => {
            e.preventDefault();
            const url = form.action;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            try {
                await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    }
                });

                loadFeedbackList(feedbackIndexUrl);
            } catch (err) {
                console.error('Could not delete feedback:', err);
            }
        });
    });
}

/**
 * Reloads the feedback list from the server using fetch.
 * Called after sending, updating, or deleting feedback.
 */
async function loadFeedbackList(url) {
    const container = document.getElementById('feedback-list');
    if (!url || !container) return;

    try {
        const response = await fetch(url);
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newList = doc.querySelector('#feedback-list');

        if (newList) {
            container.innerHTML = newList.innerHTML;
            bindInlineEditEvents(url);
        }
    } catch (err) {
        console.error('Could not load feedback list:', err);
    }
}
