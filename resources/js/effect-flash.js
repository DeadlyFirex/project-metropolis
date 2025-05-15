document.addEventListener('DOMContentLoaded', () => {
    window.flashUpdatedEffects = function () {
        document.querySelectorAll('.effect-cell').forEach(cell => {
            cell.classList.add('effect-flash');

            setTimeout(() => {
                cell.classList.remove('effect-flash');
            }, 400);

            const value = parseInt(cell.dataset.value);
            cell.textContent = value > 0 ? `+${value}` : `${value}`;
        });
    };
});
