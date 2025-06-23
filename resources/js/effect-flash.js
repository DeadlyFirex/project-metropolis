document.addEventListener('DOMContentLoaded', () => {
    // Globally available function to trigger a visual flash on all effect cells
    window.flashUpdatedEffects = function () {
        document.querySelectorAll('.effect-cell').forEach(cell => {
            // Add flash animation class
            cell.classList.add('effect-flash');

            // Remove it after a short delay
            setTimeout(() => {
                cell.classList.remove('effect-flash');
            }, 400);

            // Reset the displayed value with +/- formatting
            const value = parseInt(cell.dataset.value);
            cell.textContent = value > 0 ? `+${value}` : `${value}`;
        });
    };
});
