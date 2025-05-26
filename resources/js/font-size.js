    // Apply font scale globally to the whole document
    window.applyFontScale = function(multiplier) {
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

        document.body.setAttribute('data-font-scale', multiplier);
        localStorage.setItem('fontScale', multiplier);
        window.currentFontScale = multiplier; // ✅ Set globally
    };

    // Apply font scale to a specific container only (e.g., overlay)
    window.applyFontScaleTo = function(container, multiplier) {
        const elements = container.querySelectorAll('*:not(script):not(style)');

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
    };

    // On DOM ready, wire up the toggle button
    document.addEventListener('DOMContentLoaded', () => {
        const button = document.getElementById('increaseFontBtn');
        if (!button) return;

        const normalScale = 1;
        const increasedScale = 1.7;
        let currentScale = parseFloat(localStorage.getItem('fontScale')) || normalScale;

        // Apply scale on load
        applyFontScale(currentScale);

        // Update button label
        button.textContent = currentScale > normalScale
            ? 'Tekstgrootte Verkleinen'
            : 'Tekstgrootte Vergroten';

        // Toggle on click
        button.addEventListener('click', () => {
            currentScale = currentScale === normalScale ? increasedScale : normalScale;

            applyFontScale(currentScale);

            button.textContent = currentScale === normalScale
                ? 'Tekstgrootte Vergroten'
                : 'Tekstgrootte Verkleinen';
        });
    });