<x-app-layout>
    @if(! is_null($nextExpiration))
        <script>
            const expiresMs = new Date("{{ \Carbon\Carbon::parse($nextExpiration)->toIso8601String() }}").getTime();
            const nowMs     = Date.now();
            const delay     = expiresMs - nowMs;

            if (delay > 0) {
                setTimeout(() => {
                    window.location.reload();
                }, delay);
            }
        </script>
    @endif
    <x-slot name="header">
        <meta name="feedback-index-url" content="{{ route('feedback.index') }}">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Simulatie Dashboard') }}
            </h2>
            <div class="flex gap-2">
                <button class="bg-blue-500 text-white px-4 py-2 text-sm sm:text-base rounded" id="increaseFontBtn">
                    Tekstgrootte Vergroten
                </button>
                <button onclick="downloadDashboardAsPDF()"
                    class="bg-green-600 text-white px-4 py-2 text-sm sm:text-base rounded">
                    Download als PDF
                </button>
            </div>
        </div>
    </x-slot>

    <div id="simdash-content" class="w-full bg-gray-100 min-h-screen">
        <div class="flex flex-col gap-6 px-4 sm:px-6 pt-6">
            <div class="flex flex-col lg:flex-row gap-6 items-start">
                <main id="city-grid" class="w-full max-w-full lg:max-w-[1000px] bg-white p-4 sm:p-6 rounded-2xl shadow">
                    @include('components.city-grid', ['slots' => $slots, 'clockTime' => $clockTime])
                </main>

                <div class="w-full lg:flex-1 flex flex-col gap-6">
                    <section id="module-library"
                        class="bg-white dark:bg-gray-900 px-4 py-6 rounded-2xl shadow w-full h-[400px] overflow-y-auto">
                        @include('components.library', [
                            'modules' => $modules,
                            'categories' => $categories,
                        ])
                    </section>

                    <section id="effect-view"
                        class="bg-white dark:bg-gray-900 px-4 py-6 rounded-2xl shadow w-full overflow-x-auto">
                        @include('components.calculated-effects', ['slots' => $slots, '$clockTime' => $clockTime, '$clockDate' => $clockDate])
                    </section>

                    <section id="effect-control-view"
                        class="hidden bg-white dark:bg-gray-900 px-4 py-6 rounded-2xl shadow w-full overflow-x-auto">
                        @include('components.effect-control', [
                            'all_modules' => $modules,
                            'types' => [
                                'safety' => 'Veiligheid',
                                'recreation' => 'Recreatie',
                                'climate' => 'Milieukwaliteit',
                                'facilities' => 'Voorzieningen',
                                'infrastructure' => 'Mobiliteit',
                            ],
                        ])
                    </section>
                </div>
            </div>

            <div id="activeEventsSection" class="w-full">
                <div class="bg-white dark:bg-gray-900 px-6 py-4 rounded-2xl shadow">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Actieve Events</h3>
                    <div id="activeEventsList">
                        <p class="text-gray-500 dark:text-gray-400" id="noEventsMessage">Geen actieve events</p>
                    </div>
                </div>
            </div>
            <div id="loading"
                class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex justify-center items-center">
                <div class="w-10 h-10 border-4 border-gray-200 border-t-blue-500 rounded-full animate-spin"></div>
            </div>



            <!-- Floating Button -->
            <button id="open-feedback" class="fixed bottom-6 right-6 bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-full shadow-lg z-50">
                💬 Feedback
            </button>

            <!-- Feedback Sidebar -->
            <div id="feedback-panel" class="fixed top-0 right-0 w-full max-w-md h-full bg-white dark:bg-gray-900 shadow-lg transform translate-x-full transition-transform duration-300 z-40 flex flex-col">

                <div class="p-6 flex-1 flex flex-col overflow-hidden">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Feedback</h2>
                        <button id="close-feedback"
                            class="text-4xl text-gray-500 hover:text-gray-800 dark:hover:text-white transition-transform duration-200 hover:rotate-90">
                            &times;
                        </button>
                    </div>

                    <!-- Feedback Form -->
                    <form id="feedback-form" method="POST" action="{{ route('feedback.store') }}" data-url="{{ route('feedback.store') }}">
                        @csrf
                        <textarea name="content" required class="w-full border rounded p-3 dark:bg-gray-700 dark:text-white mb-3" rows="3" placeholder="Wat wil je delen?"></textarea>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Verstuur
                        </button>
                    </form>

                    <hr class="my-4 border-gray-300 dark:border-gray-700" />

                    <!-- Scrollable Feedback List -->
                    <div id="feedback-list" class="flex-1 overflow-y-auto pr-1">
                        @include('components.feedback.list', ['feedback' => $feedback])
                    </div>
                </div>
            </div>




            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

            <script>
                /* =====================================================
                                | PDF-EXPORT  (ongewijzigd)
                                ===================================================== */
                async function downloadDashboardAsPDF() {
                    const {
                        jsPDF
                    } = window.jspdf;
                    const pdf = new jsPDF({
                        unit: 'px',
                        format: 'a4'
                    });

                    const pageWidth = pdf.internal.pageSize.getWidth();
                    const pageHeight = pdf.internal.pageSize.getHeight();
                    const padding = 40;
                    const now = new Date().toLocaleString('nl-NL');

                    let currentY = 60;
                    let pageNumber = 1;

                    const targets = [
                        document.getElementById('city-grid'),
                        document.getElementById('effect-view')
                    ];

                    const descriptions = [
                        "Overzicht van de stadsgrid met alle modules op dit moment.",
                        "Effectenweergave van de simulatie op dit moment."
                    ];

                    pdf.setFont('helvetica', 'normal');
                    pdf.setFontSize(12);

                    for (let i = 0; i < targets.length; i++) {
                        const element = targets[i];
                        if (!element) continue;

                        const canvas = await html2canvas(element, {
                            scale: 2
                        });

                        // ✂️ Snijd witruimte van bovenkant weg
                        const croppedCanvas = cropTopWhitespace(canvas, 40); // verwijder ± 40px witruimte boven

                        const imgData = croppedCanvas.toDataURL('image/png');
                        const ratio = croppedCanvas.width / croppedCanvas.height;
                        const maxWidth = pageWidth - padding * 2;
                        let imgWidth = maxWidth;
                        let imgHeight = imgWidth / ratio;

                        // Bereken beschrijvinghoogte
                        const lines = pdf.splitTextToSize(descriptions[i], maxWidth);
                        const lineHeight = 14;
                        const descriptionHeight = lines.length * lineHeight;

                        // Pas afbeeldinggrootte aan indien nodig
                        const maxImgHeight = pageHeight - padding * 2 - descriptionHeight - 20;
                        if (imgHeight > maxImgHeight) {
                            imgHeight = maxImgHeight;
                            imgWidth = imgHeight * ratio;
                        }

                        // Check of alles op de pagina past
                        if (currentY + descriptionHeight + imgHeight > pageHeight - padding) {
                            addFooter(pdf, pageWidth, pageHeight, pageNumber++);
                            pdf.addPage();
                            currentY = 60;
                            addHeader(pdf, pageWidth, now);
                        }

                        if (pageNumber === 1 && currentY === 60) {
                            addHeader(pdf, pageWidth, now);
                        }

                        // ➕ Beschrijving
                        pdf.setFont('helvetica', 'italic');
                        pdf.text(lines, padding, currentY);
                        currentY += descriptionHeight + 6;

                        // ➕ Afbeelding
                        pdf.addImage(imgData, 'PNG', (pageWidth - imgWidth) / 2, currentY, imgWidth, imgHeight);
                        currentY += imgHeight + 20;
                    }

                    addFooter(pdf, pageWidth, pageHeight, pageNumber);
                    pdf.save('simulatie-grid-en-effecten.pdf');
                }

                function addHeader(pdf, pageWidth, now) {
                    pdf.setFontSize(16);
                    pdf.setFont('helvetica', 'bold');
                    pdf.text('Simulatie Dashboard', 40, 30);

                    pdf.setFontSize(10);
                    pdf.setFont('helvetica', 'normal');
                    pdf.text(`Gegenereerd op: ${now}`, 40, 45);

                    pdf.setDrawColor(150);
                    pdf.line(40, 50, pageWidth - 40, 50);
                }

                function addFooter(pdf, pageWidth, pageHeight, pageNumber) {
                    pdf.setFontSize(9);
                    pdf.setTextColor(150);
                    pdf.text(`Pagina ${pageNumber}`, pageWidth / 2, pageHeight - 10, {
                        align: 'center'
                    });
                }

                // ✂️ Snijd witruimte van bovenkant canvas af
                function cropTopWhitespace(canvas, cropHeight = 40) {
                    const cropped = document.createElement('canvas');
                    cropped.width = canvas.width;
                    cropped.height = canvas.height - cropHeight;

                    const ctx = cropped.getContext('2d');
                    ctx.drawImage(canvas, 0, cropHeight, canvas.width, cropped.height, 0, 0, canvas.width, cropped.height);

                    return cropped;
                }


                const SECS_DAY = 86400;
                let currentSimSec = 0;                              // simulatie-seconden (0-86399)
                let prevActiveIds = new Set();                      // track actieve slot IDs
                const CURRENT_TIME_ENDPOINT = '/user-clock/current';

                // Zet een "HH:MM:SS" string om naar seconden
                const hmsToSec = hms => {
                    const [h, m, s] = hms.split(':').map(Number);
                    return h * 3600 + m * 60 + s;
                };

                // Geef een timer-tekst weer in uren/minuten
                const secToMinTxt = sec => {
                    const hours = Math.floor(sec / 3600);
                    const minutes = Math.floor((sec % 3600) / 60);
                    return hours > 0 ? `${hours}u ${minutes}m` : `${minutes}m`;
                };

                // Haal huidige simulatie-tijd op
                async function fetchCurrentSimSec() {
                    try {
                        const resp = await fetch(CURRENT_TIME_ENDPOINT, { cache: 'no-store' });
                        if (!resp.ok) throw new Error(resp.statusText);

                        const data = await resp.json();         // JSON parsen
                        const timeStr = data.time.trim();       // "HH:MM:SS"
                        window.currentTime = timeStr;
                        console.log(`Current time fetched: ${timeStr}`);
                        return hmsToSec(timeStr);
                    } catch (err) {
                        console.error('Kon huidige kloktijd niet ophalen:', err);
                        // fallback: simuleer door 1 seconde door te tellen
                        const fallback = (currentSimSec + 1) % SECS_DAY;
                        console.log(`Using fallback time: ${secToMinTxt(fallback)}`);
                        return fallback;
                    }
                }

                // Update de countdown-teksten per seconde
                async function tickClock() {
                    console.log('tickClock called');
                    currentSimSec = await fetchCurrentSimSec();

                    document.querySelectorAll('[data-end-sec]').forEach(span => {
                        let end = Number(span.dataset.endSec);
                        let diff = end - currentSimSec;
                        if (diff < 0) diff += SECS_DAY;
                        span.textContent = secToMinTxt(diff);
                    });
                }

                // Haal events op en render alleen bij overgang in/uit actief
                async function updateActiveEvents() {
                    console.log('updateActiveEvents called');
                    try {
                        const resp = await fetch('/events/slot-events?time=' + window.currentTime, { cache: 'no-store' });
                        if (!resp.ok) throw new Error(resp.statusText);

                        const data = await resp.json();
                        const rawList = Array.isArray(data) ? data : Object.values(data);
                        const toShow = [];

                        rawList.forEach(ev => {
                            if (ev.name?.includes('(Aangrenzend)')) return;

                            let startSec = hmsToSec(ev.start_time);
                            let endSec   = hmsToSec(ev.end_time);
                            let nowSec   = currentSimSec;

                            // wrap rond middernacht
                            if (endSec < startSec) {
                                if (nowSec < startSec) nowSec += SECS_DAY;
                                endSec += SECS_DAY;
                            }

                            // alleen events waar we in het tijdslot zitten
                            if (nowSec >= startSec && nowSec <= endSec) {
                                toShow.push({ ...ev, startSec, endSec });
                            }
                        });

                        // bepaal nieuwe set actieve slot IDs
                        const newActiveIds = new Set(toShow.map(ev => ev.slot_id));
                        const sameSize = newActiveIds.size === prevActiveIds.size;
                        const sameContent = [...newActiveIds].every(id => prevActiveIds.has(id));

                        // alleen bij overgang: niet eerder en niet later
                        if (sameSize && sameContent) return;

                        // update prevActiveIds
                        prevActiveIds = newActiveIds;

                        // bouw HTML voor actieve events
                        const box = document.getElementById('activeEventsList');
                        if (toShow.length === 0) {
                            box.innerHTML = '<p class="text-gray-500 dark:text-gray-400">Geen actieve events</p>';
                            return;
                        }

                        let html = '';
                        toShow.forEach(ev => {
                            const nowSec = currentSimSec;
                            let diff = ev.endSec - nowSec;
                            html += `
  <div class="flex items-center justify-between p-3 mb-2 bg-yellow-50 dark:bg-yellow-900/20
              border border-yellow-200 dark:border-yellow-700 rounded-lg">
    <div class="flex items-center space-x-4">
      <div class="bg-yellow-500 text-white px-2 py-1 rounded text-sm font-medium">
        Vakje ${ev.slot_id}
      </div>
      <div>
        <span class="font-medium text-gray-800 dark:text-gray-200">${ev.name}</span>
        <div class="text-sm text-gray-600 dark:text-gray-400">
          Nog <span class="time-left" data-end-sec="${ev.endSec}">${secToMinTxt(diff)}</span> resterend
          ${ev.is_recurring
                                ? '<span class="ml-2 bg-blue-200 text-blue-800 px-2 py-1 rounded text-xs">Terugkerend</span>'
                                : ''}
        </div>
      </div>
    </div>
    <div class="flex items-center space-x-2">
      <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse" title="Actief"></div>
      <a href="/events?time=${window.currentTime}"
         class="text-blue-600 hover:text-blue-800 text-sm">Beheren</a>
    </div>
  </div>`;
                        });

                        box.innerHTML = html;

                    } catch (err) {
                        console.error('Error fetching events:', err);
                    }
                }

                // INIT: start klok & event-checks
                document.addEventListener('DOMContentLoaded', async () => {
                    currentSimSec = await fetchCurrentSimSec();  // startwaarde

                    // init prevActiveIds voor eerste check
                    await updateActiveEvents();                   // meteen check en render

                    setInterval(tickClock, 1000);                // countdown elke seconde
                    console.log('Scheduled tickClock every 1 second');

                    setInterval(updateActiveEvents, 1000);      // check overgang elke seconde
                    console.log('Scheduled updateActiveEvents every 1 second');
                });


            </script>



            <style>
                .slot-with-event {
                    position: relative;
                    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.5) !important;
                }

                .slot-with-event::after {
                    content: '⚡';
                    position: absolute;
                    top: -8px;
                    right: -8px;
                    background: #f59e0b;
                    color: white;
                    border-radius: 50%;
                    width: 20px;
                    height: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 12px;
                    z-index: 10;
                }

                [x-cloak] {
                    display: none !important;
                }

                .pdf-hide {
                    display: none !important;
                }
            </style>
</x-app-layout>
