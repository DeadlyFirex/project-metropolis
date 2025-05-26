function applyFontScale(multiplier) {
    const elements = document.querySelectorAll('body *');

    elements.forEach((el) => {
        if (el.children.length === 0 && el.textContent.trim() !== '') {
            let originalSize = el.getAttribute('data-original-font-size');

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

    // ⬇️ Nieuw: zet attribuut op body
    document.body.setAttribute('data-font-scale', multiplier);
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
    button.textContent = currentScale > normalScale ? 'Tekstgrootte Verkleinen' : 'Tekstgrootte Vergroten';

    button.addEventListener('click', () => {
        currentScale = currentScale === normalScale ? increasedScale : normalScale;
        button.textContent = currentScale === normalScale ? 'Tekstgrootte Vergroten' : 'Tekstgrootte Verkleinen';

        applyFontScale(currentScale);
    });
});
