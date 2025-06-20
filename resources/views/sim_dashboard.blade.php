@php use Carbon\Carbon; @endphp
<x-app-layout>
    @if(! is_null($nextExpiration))
        <script>
            const expiresMs = new Date("{{ Carbon::parse($nextExpiration)->toIso8601String() }}").getTime();
            const nowMs = Date.now();
            const delay = expiresMs - nowMs;

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
            <button id="open-feedback"
                    class="fixed bottom-6 right-6 bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-full shadow-lg z-50">
                💬 Feedback
            </button>

            <!-- Feedback Sidebar -->
            <div id="feedback-panel"
                 class="fixed top-0 right-0 w-full max-w-md h-full bg-white dark:bg-gray-900 shadow-lg transform translate-x-full transition-transform duration-300 z-40 flex flex-col">

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
                    <form id="feedback-form" method="POST" action="{{ route('feedback.store') }}"
                          data-url="{{ route('feedback.store') }}">
                        @csrf
                        <textarea name="content" required
                                  class="w-full border rounded p-3 dark:bg-gray-700 dark:text-white mb-3" rows="3"
                                  placeholder="Wat wil je delen?"></textarea>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Verstuur
                        </button>
                    </form>

                    <hr class="my-4 border-gray-300 dark:border-gray-700"/>

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
                    const {jsPDF} = window.jspdf;
                    const pdf = new jsPDF({unit: 'px', format: 'a4'});

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

                function addHeader(pdf, w, now) {
                    pdf.setFontSize(16);
                    pdf.setFont('helvetica', 'bold');
                    pdf.text('Simulatie Dashboard', 40, 30);

                    pdf.setFontSize(10);
                    pdf.setFont('helvetica', 'normal');
                    pdf.text(`Gegenereerd op: ${now}`, 40, 45);

                    pdf.setDrawColor(150);
                    pdf.line(40, 50, w - 40, 50);
                }

                function addFooter(pdf, w, h, p) {
                    pdf.setFontSize(9);
                    pdf.setTextColor(150);
                    pdf.text(`Pagina ${p}`, w / 2, h - 10, {align: 'center'});
                }


                /* =====================================================
                                | HULPFUNCTIES
                                ===================================================== */
                const SECS_DAY = 86400;

                const hmsToSec = hms => {
                    const [h, m, s] = hms.split(':').map(Number);
                    return h * 3600 + m * 60 + s;
                };

                const secToMinTxt = sec => {
                    const hours = Math.floor(sec / 3600);
                    const minutes = Math.floor((sec % 3600) / 60);

                    if (hours > 0) {
                        return `${hours}u ${minutes}m`;
                    } else {
                        return `${minutes}m`;
                    }
                };

                /* =====================================================
                 | VARIABELEN
                 ===================================================== */
                let currentSimSec = 0;                                // simulatie-seconden (0-86399)
                const CURRENT_TIME_ENDPOINT = '/user-clock/current';  // route uit de controller

                /* =====================================================
                 | HUIDIGE TIJD OPHALEN
                 ===================================================== */
                async function fetchCurrentSimSec() {
                    try {
                        const resp = await fetch(CURRENT_TIME_ENDPOINT, {cache: 'no-store'});
                        if (!resp.ok) throw new Error(resp.statusText);

                        const data = await resp.json();      // 👉 JSON parsen
                        const timeStr = data.time.trim();    // "HH:MM:SS"

                        window.currentTime = timeStr;
                        return hmsToSec(timeStr);
                    } catch (err) {
                        console.error('Kon huidige kloktijd niet ophalen:', err);
                        // fallback…
                        return (currentSimSec + 1) % SECS_DAY;
                    }
                }

                /* =====================================================
                 | SECONDE-TIK (UPDATE COUNTDOWN)
                 ===================================================== */
                async function tickClock() {
                    currentSimSec = await fetchCurrentSimSec();

                    document.querySelectorAll('[data-end-sec]').forEach(span => {
                        let end = Number(span.dataset.endSec);
                        let diff = end - currentSimSec;
                        if (diff < 0) diff += SECS_DAY;
                        span.textContent = secToMinTxt(diff);
                    });
                }

                /* =====================================================
                 | EVENT-LIJST OPHALEN & TONEN
                 ===================================================== */
                function updateActiveEvents() {
                    fetch('/events/slot-events?time=' + window.currentTime, {cache: 'no-store'})
                        .then(r => r.json())
                        .then(data => {
                            const box = document.getElementById('activeEventsList');
                            const list = Array.isArray(data) ? data : Object.values(data);

                            if (list.length === 0) {
                                box.innerHTML =
                                    '<p class="text-gray-500 dark:text-gray-400">Geen actieve events</p>';
                                return;
                            }

                            let html = '';
                            list.forEach(ev => {
                                // 1) filter naam “(Aangrenzend)”
                                if (ev.name?.includes('(Aangrenzend)')) return;

                                // 2) bereken begin- én eind-seconden
                                const startSec = hmsToSec(ev.start_time);
                                const endSec = hmsToSec(ev.end_time);
                                let nowSec = currentSimSec;

                                // 3) wrap rond middernacht
                                if (endSec < startSec) {
                                    // bijvoorbeeld start 23:00, eind 01:00 → treat als 23→25h
                                    if (nowSec < startSec) nowSec += SECS_DAY;
                                    endSec += SECS_DAY;
                                }

                                // 4) sla events over waar we niet in het tijdslot zitten
                                setInterval(() => { 
                                    if (nowSec < startSec || nowSec > endSec) return;
                                // Your code to execute during the interval goes here
                                    console.log("Event should be in active events.");
                                    }, 2000); // Run every 1000ms (2.5 second)


                                // 5) nu pas de HTML bouwen
                                const diff = endSec - nowSec;
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
        Nog <span class="time-left" data-end-sec="${endSec}">${secToMinTxt(diff)}</span> resterend
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

                            box.innerHTML = html || '<p class="text-gray-500 dark:text-gray-400">Geen actieve events</p>';
                        })
                        .catch(err => console.error('Error fetching events:', err));
                }

                /* =====================================================
                 | INIT
                 ===================================================== */
                document.addEventListener('DOMContentLoaded', async () => {
                    currentSimSec = await fetchCurrentSimSec();  // startwaarde ophalen

                    updateActiveEvents();                    // meteen tonen
                    setInterval(updateActiveEvents, 60000);  // lijst elke minuut vernieuwen
                    setInterval(tickClock, 1000);            // countdown elke seconde up-to-date
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
