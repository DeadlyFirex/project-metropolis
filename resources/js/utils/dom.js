export function showLoading() {
    document.getElementById('loading').style.display = 'flex';
}

export function hideLoading() {
    document.getElementById('loading').style.display = 'none';
}

export function clearSelections() {
    document.querySelectorAll('.module-card').forEach(c => c.classList.remove('selected'));
    document.querySelectorAll('.city-slot').forEach(s => s.classList.remove('selected'));
}

export function clearAllListeners() {
    document.querySelectorAll('.module-card, .city-slot').forEach(el => {
        const clone = el.cloneNode(true);
        el.replaceWith(clone);
    });
}

export function removeInlineModulePickers() {
    document.querySelectorAll('.inline-module-picker').forEach(p => p.remove());
}
