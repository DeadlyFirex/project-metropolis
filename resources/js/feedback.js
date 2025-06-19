export function initFeedback() {
    const openBtn = document.getElementById('open-feedback');
    const closeBtn = document.getElementById('close-feedback');
    const panel = document.getElementById('feedback-panel');
    const feedbackForm = document.getElementById('feedback-form');
    const feedbackIndexUrl = document.querySelector('meta[name="feedback-index-url"]')?.content;

    if (openBtn && closeBtn && panel) {
        openBtn.addEventListener('click', () => {
            panel.classList.remove('translate-x-full');
        });

        closeBtn.addEventListener('click', () => {
            panel.classList.add('translate-x-full');
        });
    }

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
                loadFeedbackList(feedbackIndexUrl);
            } catch (error) {
                console.error('Fout bij versturen van feedback:', error);
            }
        });
    }

    bindInlineEditEvents(feedbackIndexUrl);
}

function bindInlineEditEvents(feedbackIndexUrl) {
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.dataset.editId;
            const wrapper = document.querySelector(`div[data-id="${id}"]`);
            wrapper.querySelector('.view-mode')?.classList.add('hidden');
            wrapper.querySelector('.feedback-edit-form')?.classList.remove('hidden');
        });
    });

    document.querySelectorAll('.cancel-edit-btn').forEach(button => {
        button.addEventListener('click', () => {
            const wrapper = button.closest('div[data-id]');
            wrapper.querySelector('.view-mode')?.classList.remove('hidden');
            wrapper.querySelector('.feedback-edit-form')?.classList.add('hidden');
        });
    });

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
                console.error('Update mislukt:', err);
            }
        });
    });

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
                console.error('Verwijderen mislukt:', err);
            }
        });
    });
}

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
        console.error('Kon feedbacklijst niet ophalen:', err);
    }
}
