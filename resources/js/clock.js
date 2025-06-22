/**
 * SimulationClock class for managing a simulation clock in a web application.
 * This class handles time and date management, UI updates, and event fetching.
 * @module SimulationClock
 * @see {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Classes|MDN Classes}
 */
class SimulationClock {
    #intervalId = null;
    #lastSave = 0;
    #saveInterval = 2500;

    /**
     * Creates an instance of SimulationClock.
     * Initializes the clock with provided or default selectors and settings.
     * @param {string} [clockSelector='#clock'] - Selector for the clock element.
     * @param {string} [dateSelector='#date'] - Selector for the date element.
     * @param {string} [modeIconSelector='#mode-icon'] - Selector for the night mode icon.
     * @param {string} [csrfSelector='meta[name="csrf-token"]'] - Selector for the CSRF token meta tag.
     * @param {string|null} [initialTime=null] - Initial time in HH:MM:SS format.
     * @param {Date|null} [initialDate=null] - Initial date as a Date object.
     * @param {number} [tickRate=1000] - Interval in milliseconds for clock ticks.
     */
    constructor({
        clockSelector = '#clock',
        dateSelector = '#date',
        modeIconSelector = '#mode-icon',
        csrfSelector = 'meta[name="csrf-token"]',
        initialTime = null,
        initialDate = null,
        tickRate = 1000
    } = {}) {
        this.clockEl = document.querySelector(clockSelector);
        this.dateEl = document.querySelector(dateSelector);
        this.modeIcon = document.querySelector(modeIconSelector);
        this.csrfToken = document.querySelector(csrfSelector)?.getAttribute('content');
        this.slotEventsUrl = document.querySelector('meta[name="slot-events-url"]')?.getAttribute('content');
        this.clockSaveUrl = document.querySelector('meta[name="clock-save-url"]')?.getAttribute('content');

        this.currentTime = initialTime ?? this.clockEl?.dataset?.start ?? '00:00:00';
        this.currentDate = initialDate ?? new Date(this.dateEl?.dataset?.start ?? Date.now());
        this.interval = tickRate;

        this.updateClockDisplay();
        this.updateInterval(this.interval);
    }

    pad = num => String(num).padStart(2, '0');

    /**
     * Updates the clock and date display elements with the current time and date.
     * This method is called whenever the time or date changes,
     * ensuring the UI reflects the current simulation state.
     *
     * @returns {void}
     */
    updateClockDisplay = () => {
        if (this.clockEl) this.clockEl.innerText = this.currentTime;
        if (this.dateEl) this.dateEl.innerText = this.currentDate.toLocaleDateString('nl-NL');
    }

    /**
     * Simulates the clock ticking forward by one second.
     * This updates the current time and date, and logs the new date if it changes.
     *
     * @returns {void}
     */
    tickClock = () => {
        let [h, m, s] = this.currentTime.split(':').map(Number);
        s++;
        if (s >= 60) { s = 0; m++; }
        if (m >= 60) { m = 0; h++; }
        if (h >= 24) { h = 0;
            this.currentDate.setDate(this.currentDate.getDate() + 1);
            console.log(`📅 Nieuw gesimuleerde datum: ${this.currentDate.toDateString()}`);
        }
        this.currentTime = `${this.pad(h)}:${this.pad(m)}:${this.pad(s)}`;
        this.updateClockDisplay();
    };

    /**
     * Updates the UI by saving the current time and date,
     * fetching slot events, and updating the night mode icon.
     * This method is called periodically based on the configured interval.
     * @returns {void}
     */
    updateUI = () => {
        // Prevent saving too frequently
        if (Date.now() - this.#lastSave < this.#saveInterval) return;

        // Save the current time and date
        this.#lastSave = Date.now();
        fetch(this.clockSaveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                time: this.currentTime,
                date: this.currentDate.toISOString().slice(0, 10)
            })
        }).catch(e => console.error('❌ Tijd opslaan mislukt', e));
        console.log(`🕒 Tijd opgeslagen: ${this.currentTime}`);

        // Fetch and update slot events
        fetch(`${this.slotEventsUrl}?time=${this.currentTime}`)
            .then(r => r.json())
            .then(this.updateGrid)
            .catch(e => console.error('❌ Fout bij het ophalen van evenementen:', e));

        // Update night mode based on current time
        if (!this.modeIcon) return;
        const hour = parseInt(this.currentTime.split(':')[0], 10);
        document.body.classList.toggle('night-mode', hour >= 22 || hour < 8);
        setTimeout(() => {
            this.modeIcon.textContent = document.body.classList.contains('night-mode') ? '🌙' : '🌞';
        }, 200);
    };

    /**
     * Updates the grid of city slots with event data.
     * This method clears existing event data from the slots
     * and populates them with new event information.
     * @param events {Array} - Array of event objects to update the grid with.
     * @returns {void}
     */
    updateGrid = (events) => {
        document.querySelectorAll('.city-slot[data-event-id]').forEach(slot => {
            slot.removeAttribute('data-event-id');
            slot.removeAttribute('data-event-name');
            slot.removeAttribute('data-event-image');
            slot.querySelector('.event-badge')?.remove();
        });

        events.forEach(ev => {
            const slot = document.querySelector(`.city-slot[data-slot-id="${ev.slot_id}"]`);
            if (!slot) return;

            slot.dataset.eventId = ev.id;
            slot.dataset.eventName = ev.name;
            if (ev.image_path) {
                slot.dataset.eventImage = ev.image_path.startsWith('http')
                    ? ev.image_path
                    : `{{ url('/') }}/${ev.image_path}`;
            }
        });
    };

    /**
     * Updates the clock tick interval.
     * This method clears any existing interval and sets a new one based on the provided interval in milliseconds.
     * @param intervalMs {number} - The new interval in milliseconds for the clock ticks.
     * @returns {void}
     */
    updateInterval = (intervalMs) => {
        if (this.#intervalId) clearInterval(this.#intervalId);
        this.#intervalId = null;

        if (!intervalMs || intervalMs <= 0) return;

        this.interval = intervalMs;

        const handler = () => {
            try {
                this.tickClock();
                this.updateUI();
            } catch (err) {
                console.error('⛔ Interval-executie fout:', err);
            }
        };

        this.#intervalId = setInterval(handler, this.interval);
    };

    /**
     * Pauses the simulation clock.
     * This method stops the clock from ticking and logs a message to the console.
     * @returns {void}
     */
    pause = () => {
        this.updateInterval(0);
        console.log("⏸ Simulatie gepauzeerd");
    };

    /**
     * Resumes the simulation clock.
     * This method restarts the clock with the previously set interval
     * and logs a message to the console.
     * @returns {void}
     */
    resume = () => {
        this.updateInterval(this.interval || 1000);
        console.log("▶ Simulatie hervat");
    };

    /**
     * Accelerates the simulation clock by a specified factor.
     * This method changes the clock's tick rate to a faster interval,
     * allowing the simulation to run at an accelerated pace.
     * @param {number} [factor=2] - The factor by which to accelerate the clock.
     * @returns {void}
     */
    accelerate = (factor = 2) => {
        const newInterval = 1000 / factor;
        this.updateInterval(newInterval);
        console.log(`⏩ Versneld (${factor}x)`);
    };

    /**
     * Skips forward in time by a specified number of minutes.
     * This method adjusts the current time by adding the specified number of minutes,
     * ensuring the time wraps correctly at 60 minutes.
     * This is useful for simulating time passage without waiting.
     * @param {number} [minutes=1] - The number of minutes to skip forward.
     * @returns {void}
     */
    skipMinutes = (minutes = 1) => {
        if (minutes < 1 || minutes > 60) {
            console.warn('⏭ Ongeldige minuten opgegeven, moet tussen 1 en 60 liggen.');
            return;
        }

        let [h, m, s] = this.currentTime.split(':').map(Number);
        m += minutes;
        if (m >= 60) {
            h += Math.floor(m / 60);
            m = m % 60;
        }
        h = h % 24;
        this.currentTime = `${this.pad(h)}:${this.pad(m)}:${this.pad(s)}`;
        this.updateClockDisplay();
        this.updateUI();
        console.log(`⏭ ${minutes} minuten vooruit gesprongen naar ${this.currentTime}`);
    }

    /**
     * Skips forward in time by a specified number of hours.
     * This method adjusts the current time by adding the specified number of hours,
     * ensuring the time wraps correctly at 24 hours.
     * This is useful for simulating longer time passages without waiting.
     * @param {number} [hours=1] - The number of hours to skip forward.
     * @returns {void}
     */
    skipHours = (hours = 1) => {
        if (hours < 1 || hours > 24) {
            console.warn('⏭ Ongeldige uren opgegeven, moet tussen 1 en 24 liggen.');
            return;
        }

        let [h, m, s] = this.currentTime.split(':').map(Number);
        h = (h + hours) % 24;
        this.currentTime = `${this.pad(h)}:${this.pad(m)}:${this.pad(s)}`;
        this.updateClockDisplay();
        this.updateUI();
        console.log(`⏭ ${hours} uur vooruit gesprongen naar ${this.currentTime}`);
    };
}
if (typeof window !== 'undefined') {
    // Initialize the clock with default settings
    window.SimulationClock = SimulationClock;
    const clock = new SimulationClock();

    window.pauseClock = () => clock.pause();
    window.resumeClock = () => clock.resume();
    window.accelerateClock = (factor) => clock.accelerate(factor);
    window.skipMinutes = (minutes) => clock.skipMinutes(minutes);
    window.skipHours = (hours) => clock.skipHours(hours);
}
