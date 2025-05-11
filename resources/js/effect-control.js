document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('[data-action="effect-adjust"]').forEach(button => {
        button.addEventListener('click', () => {
            const moduleId = button.dataset.moduleId;
            const type = button.dataset.effectType;
            const delta = parseInt(button.dataset.delta);
            const valueEl = document.querySelector(
                `.effect-value[data-module="${moduleId}"][data-type="${type}"]`
            );
            const current = parseInt(valueEl.textContent);
            const newValue = Math.max(-5, Math.min(5, current + delta));

            fetch(`/effects/module/${moduleId}/${type}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ value: newValue })
            })
            .then(res => {
                if (!res.ok) throw new Error('Update mislukt');
                valueEl.textContent = newValue;

                const flashClass = delta > 0 ? 'effect-flash-up' : 'effect-flash-down';
                valueEl.classList.add(flashClass);
                setTimeout(() => {
                    valueEl.classList.remove(flashClass);
                }, 400);
            })
            .catch(err => {
                alert("Effect kon niet worden aangepast: " + err.message);
            });
        });
    });
});
