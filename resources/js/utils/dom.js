/**
 * Shows the global loading overlay by setting display to 'flex'.
 */
export function showLoading() {
    document.getElementById('loading').style.display = 'flex';
}

/**
 * Hides the global loading overlay by setting display to 'none'.
 */
export function hideLoading() {
    document.getElementById('loading').style.display = 'none';
}

/**
 * Removes all visual selection indicators from modules and slots.
 */
export function clearSelections() {
    document.querySelectorAll('.module-card').forEach(c => c.classList.remove('selected'));
    document.querySelectorAll('.city-slot').forEach(s => s.classList.remove('selected'));
}

/**
 * Completely removes all event listeners from modules and slots
 * by replacing each element with a cloned copy.
 */
export function clearAllListeners() {
    document.querySelectorAll('.module-card, .city-slot').forEach(el => {
        const clone = el.cloneNode(true);
        el.replaceWith(clone);
    });
}

/**
 * Removes all visible inline module picker elements from the DOM.
 */
export function removeInlineModulePickers() {
    document.querySelectorAll('.inline-module-picker').forEach(p => p.remove());
}
