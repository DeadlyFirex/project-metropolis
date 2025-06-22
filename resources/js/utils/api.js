import { showLoading, hideLoading } from './dom.js';

// Fetch meta content for CSRF protection and endpoint URLs
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
const attachUrl = document.querySelector('meta[name="attach-module-url"]')?.content;
const moveUrl = document.querySelector('meta[name="move-module-url"]')?.content;

/**
 * Sends a request to attach a module to a specific slot.
 * Shows a loading indicator and handles various error responses.
 */
export async function attachModule(moduleId, slotId) {
    showLoading();
    try {
        const res = await fetch(attachUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ module_id: moduleId, slot_id: slotId })
        });

        hideLoading();

        if (res.status === 422) return alert("Je mag deze categorieën niet naast elkaar hebben.");
        if (res.status === 409) return alert("Je mag niet meer van deze categorie neerzetten.");
        if (!res.ok) return alert("Er is iets misgegaan bij het koppelen.");

        location.reload();
    } catch {
        hideLoading();
        alert("Er is iets misgegaan bij het koppelen.");
    }
}

/**
 * Sends a request to move a module from one slot to another.
 * Shows a loading indicator and reloads the page on success.
 */
export async function moveModule(moduleId, fromSlotId, toSlotId) {
    showLoading();
    try {
        const res = await fetch(moveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ module_id: moduleId, from_slot_id: fromSlotId, to_slot_id: toSlotId })
        });

        hideLoading();
        if (!res.ok) return alert("Er is iets misgegaan bij het verplaatsen.");
        location.reload();
    } catch {
        hideLoading();
        alert("Er is iets misgegaan bij het verplaatsen.");
    }
}
