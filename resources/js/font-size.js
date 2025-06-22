    // Apply font scale globally to the whole document
    // Multiplier (e.g. 1.7) enlarges all text proportionally
    window.applyFontScale = function(multiplier) {
        const elements = document.querySelectorAll('body *');

        elements.forEach((el) => {
            // Only target leaf nodes with visible text
            if (el.children.length === 0 && el.textContent.trim() !== '') {
                let originalSize = el.getAttribute('data-original-font-size');

                // Save original size only once
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

        // Save scale in DOM and localStorage
        document.body.setAttribute('data-font-scale', multiplier);
        localStorage.setItem('fontScale', multiplier);
        window.currentFontScale = multiplier;
    };

    // Apply font scale only to a given container
    // Useful for modals or overlays without affecting whole document
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

        // Apply saved or default scale
        applyFontScale(currentScale);

        // Update button label to reflect state
        button.textContent = currentScale > normalScale
            ? 'Aa'
            : 'aA';

        // Toggle font size on button click
        button.addEventListener('click', () => {
            currentScale = currentScale === normalScale ? increasedScale : normalScale;

            applyFontScale(currentScale);

            button.textContent = currentScale === normalScale
                ? 'aA'
                : 'Aa';
        });
    });
