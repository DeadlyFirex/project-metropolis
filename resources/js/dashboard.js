import { initModuleDragAndDrop } from './services/modules.js';
import { initLibrarySearch } from './services/library.js';
import { initFeedback } from './services/feedback.js';
import { downloadDashboardAsPDF } from './utils/pdf.js';
import { hmsToSec, secToMinTxt, setsAreEqual, SECS_DAY } from './utils/time.js';

let currentSimSec = null;
let prevActiveIds = new Set();

// Fetch endpoint URLs from meta tags
const endpoints = {
    currentTime: document.querySelector('meta[name="clock-current-url"]')?.getAttribute('content'),
    effects: document.querySelector('meta[name="dashboard-effects-url"]')?.getAttribute('content'),
    events: document.querySelector('meta[name="slot-events-url"]')?.getAttribute('content'),
};

// Initialize everything once DOM is ready
document.addEventListener('DOMContentLoaded', async () => {
    setupUI();

    currentSimSec = await updateClock();

    // Keep the dashboard in sync with server state
    setInterval(updateUI, 1000);
    setInterval(updateActiveEvents, 1000);
    setInterval(updateCalculatedEffects, 1000);

    // Init module behavior, search, and feedback panel
    initModuleDragAndDrop();
    initLibrarySearch();
    initFeedback();
});

/**
 * Sets up UI interactions like PDF export button.
 */
function setupUI() {
    const pdfBtn = document.getElementsByName('downloadPdfBtn')[0];
    if (pdfBtn) pdfBtn.addEventListener('click', downloadDashboardAsPDF);
}

/**
 * Updates all timer countdowns (e.g. remaining time for events).
 */
async function updateUI() {
    currentSimSec = await updateClock();

    document.querySelectorAll('[data-end-sec]').forEach(span => {
        const end = Number(span.dataset.endSec);
        let diff = end - currentSimSec;
        if (diff < 0) diff += SECS_DAY;
        span.textContent = secToMinTxt(diff);
    });
}

/**
 * Fetches the current simulation time from the backend.
 * If it fails, simulates time by incrementing the previous value.
 */
async function updateClock() {
    try {
        const resp = await fetch(endpoints.currentTime, { cache: 'no-store' });
        const data = await resp.json();
        const timeStr = data.time.trim();
        window.currentTime = timeStr;
        return hmsToSec(timeStr);
    } catch {
        return (currentSimSec + 1) % SECS_DAY;
    }
}

/**
 * Updates the list of currently active events based on simulation time.
 * Highlights active slots, updates UI only if the active set changes.
 */
async function updateActiveEvents() {
    try {
        const resp = await fetch(`${endpoints.events}?time=${window.currentTime}`, { cache: 'no-store' });
        const data = await resp.json();
        const events = Array.isArray(data) ? data : Object.values(data);

        // Filter only primary (non-adjacent) events
        const active = events.filter(ev => {
            if (ev.name?.includes('(Aangrenzend)')) return false;

            let start = hmsToSec(ev.start_time);
            let end = hmsToSec(ev.end_time);
            let now = currentSimSec;

            if (end < start) {
                if (now < start) now += SECS_DAY;
                end += SECS_DAY;
            }

            return now >= start && now <= end;
        });

        const newIds = new Set(active.map(ev => ev.slot_id));
        if (setsAreEqual(prevActiveIds, newIds)) return;

        prevActiveIds = newIds;
        const box = document.getElementById('activeEventsList');

        // Display "no active events" message
        if (!active.length) {
            box.innerHTML = '<p class="text-gray-500 dark:text-gray-400">Geen actieve events</p>';
            return;
        }

        // Render list of active events
        box.innerHTML = active.map(ev => {
            let startSec = hmsToSec(ev.start_time);
            let endSec = hmsToSec(ev.end_time);

            // Handle day wrap
            if (endSec < startSec) endSec += SECS_DAY;

            let diff = endSec - currentSimSec;
            if (diff < 0) diff += SECS_DAY;

            return `
            <div class="flex items-center justify-between p-3 mb-2 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="bg-yellow-500 text-white px-2 py-1 rounded text-sm font-medium">Vakje ${ev.slot_id}</div>
                    <div>
                        <span class="font-medium text-gray-800 dark:text-gray-200">${ev.name}</span>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Nog <span class="time-left" data-end-sec="${endSec}">${secToMinTxt(diff)}</span> resterend
                            ${ev.is_recurring ? '<span class="ml-2 bg-blue-200 text-blue-800 px-2 py-1 rounded text-xs">Terugkerend</span>' : ''}
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse" title="Actief"></div>
                    <a href="/events?time=${window.currentTime}" class="text-blue-600 hover:text-blue-800 text-sm">Beheren</a>
                </div>
            </div>`;
        }).join('');
    } catch (err) {
        console.error('Error fetching events:', err);
    }
}

/**
 * Reloads the calculated effects panel from the server.
 * Called every second to stay synced with changes.
 */
async function updateCalculatedEffects() {
    try {
        const resp = await fetch(`${endpoints.effects}?time=${window.currentTime}`, { cache: 'no-store' });
        document.getElementById('effect-view').innerHTML = await resp.text();
    } catch (err) {
        console.error('Failed to reload calculated effects:', err);
    }
}
