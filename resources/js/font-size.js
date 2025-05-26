function applyFontScale(multiplier) {
    const elements = document.querySelectorAll('body *');

    elements.forEach((el, i) => {
        if (el.children.length === 0 && el.textContent.trim() !== '') {
            let originalSize = el.getAttribute('data-original-font-size');

            // If not yet stored, save it
            if (!originalSize) {
                const computedSize = window.getComputedStyle(el).fontSize;
                el.setAttribute('data-original-font-size', computedSize);
                originalSize = computedSize;
            }

            const baseSize = parseFloat(originalSize);
            const newSize = baseSize * multiplier;

            el.style.fontSize = newSize + 'px';
        }
    });

    localStorage.setItem('fontScale', multiplier);
}

document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('increaseFontBtn');
    if (!button) return;

    const normalScale = 1;
    const increasedScale = 1.7;
    let currentScale = parseFloat(localStorage.getItem('fontScale')) || normalScale;

    // Apply scale to elements using stored or default multiplier
    applyFontScale(currentScale);

    // Update button label
    button.textContent = currentScale > normalScale ? 'Decrease Font Size' : 'Increase Font Size';

    button.addEventListener('click', () => {
        currentScale = currentScale === normalScale ? increasedScale : normalScale;
        button.textContent = currentScale === normalScale ? 'Increase Font Size' : 'Decrease Font Size';

        applyFontScale(currentScale);
    });
});
