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
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Simulatie Dashboard') }}
            </h2>
            <div class="flex gap-2">
                <button class="bg-blue-500 text-white px-4 py-2 text-sm sm:text-base rounded" id="increaseFontBtn">
                    Tekstgrootte Vergroten
                </button>
                <button onclick="downloadDashboardAsPDF()" class="bg-green-600 text-white px-4 py-2 text-sm sm:text-base rounded">
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
                    <section id="module-library" class="bg-white dark:bg-gray-900 px-4 py-6 rounded-2xl shadow w-full h-[400px] overflow-y-auto">
                        @include('components.library', [
                        'modules' => $modules,
                        'categories' => $categories,
                        ])
                    </section>

                    <section id="effect-view" class="bg-white dark:bg-gray-900 px-4 py-6 rounded-2xl shadow w-full overflow-x-auto">
                        @include('components.calculated-effects', ['slots' => $slots])
                    </section>

                    <section id="effect-control-view" class="hidden bg-white dark:bg-gray-900 px-4 py-6 rounded-2xl shadow w-full overflow-x-auto">
                        @include('components.effect-control', ['all_modules' => $modules, 'types' => [
                        'safety' => 'Veiligheid',
                        'recreation' => 'Recreatie',
                        'climate' => 'Milieukwaliteit',
                        'facilities' => 'Voorzieningen',
                        'infrastructure' => 'Mobiliteit',
                        ]])
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

            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

            <script>
                /* =====================================================
                 | PDF-EXPORT  (ongewijzigd)
                 ===================================================== */
                async function downloadDashboardAsPDF () {
                    const { jsPDF } = window.jspdf;
                    const pdf       = new jsPDF({ unit: 'px', format: 'a4' });

                    const pageWidth  = pdf.internal.pageSize.getWidth();
                    const pageHeight = pdf.internal.pageSize.getHeight();
                    const padding    = 40;
                    const now        = new Date().toLocaleString('nl-NL');

                    let currentY   = 60;
                    let pageNumber = 1;

                    const targets = [
                        document.getElementById('city-grid'),
                        document.getElementById('effect-view')
                    ];

                    for (const el of targets) {
                        const canvas  = await html2canvas(el, { scale: 2 });
                        const imgData = canvas.toDataURL('image/png');

                        const ratio    = canvas.width / canvas.height;
                        const maxWidth = pageWidth - padding * 2;
                        let imgWidth   = maxWidth;
                        let imgHeight  = imgWidth / ratio;

                        if (imgHeight > pageHeight - padding * 2 - 40) {
                            imgHeight = pageHeight - padding * 2 - 40;
                            imgWidth  = imgHeight * ratio;
                        }
                        if (currentY + imgHeight > pageHeight - padding) {
                            addFooter(pdf, pageWidth, pageHeight, pageNumber++);
                            pdf.addPage();
                            currentY = 60;
                            addHeader(pdf, pageWidth, now);
                        }
                        if (pageNumber === 1 && currentY === 60) {
                            addHeader(pdf, pageWidth, now);
                        }
                        pdf.addImage(imgData, 'PNG',
                            (pageWidth - imgWidth) / 2,
                            currentY,
                            imgWidth,
                            imgHeight
                        );
                        currentY += imgHeight + 20;
                    }
                    addFooter(pdf, pageWidth, pageHeight, pageNumber);
                    pdf.save('simulatie-grid-en-effecten.pdf');
                }

                function addHeader (pdf, w, now) {
                    pdf.setFontSize(16);
                    pdf.setFont('helvetica', 'bold');
                    pdf.text('Simulatie Dashboard', 40, 30);

                    pdf.setFontSize(10);
                    pdf.setFont('helvetica', 'normal');
                    pdf.text(`Gegenereerd op: ${now}`, 40, 45);

                    pdf.setDrawColor(150);
                    pdf.line(40, 50, w - 40, 50);
                }
                function addFooter (pdf, w, h, p) {
                    pdf.setFontSize(9);
                    pdf.setTextColor(150);
                    pdf.text(`Pagina ${p}`, w / 2, h - 10, { align: 'center' });
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
                    const mm = String(Math.floor(sec / 60)).padStart(2, '0');   // alleen minuten
                    return `${mm} min`;
                };

                /* =====================================================
                 | VARIABELEN
                 ===================================================== */
                let currentSimSec = 0;                                // simulatie-seconden (0-86399)
                const CURRENT_TIME_ENDPOINT = '/user-clock/current';  // route uit de controller

                /* =====================================================
                 | HUIDIGE TIJD OPHALEN
                 ===================================================== */
                async function fetchCurrentSimSec () {
                    try {
                        const resp = await fetch(CURRENT_TIME_ENDPOINT, { cache: 'no-store' });
                        if (!resp.ok) throw new Error(resp.statusText);
                        const timeStr = (await resp.text()).trim();       // verwacht “HH:MM:SS”
                        window.currentTime = timeStr;
                        return hmsToSec(timeStr);
                    } catch (err) {
                        console.error('Kon huidige kloktijd niet ophalen:', err);
                        // Fallback: ga lokaal één seconde vooruit
                        return (currentSimSec + 1) % SECS_DAY;
                    }
                }

                /* =====================================================
                 | SECONDE-TIK (UPDATE COUNTDOWN)
                 ===================================================== */
                async function tickClock () {
                    currentSimSec = await fetchCurrentSimSec();

                    document.querySelectorAll('[data-end-sec]').forEach(span => {
                        let end  = Number(span.dataset.endSec);
                        let diff = end - currentSimSec;
                        if (diff < 0) diff += SECS_DAY;
                        span.textContent = secToMinTxt(diff);
                    });
                }

                /* =====================================================
                 | EVENT-LIJST OPHALEN & TONEN
                 ===================================================== */
                function updateActiveEvents () {
                    fetch('/events/slot-events?time=' + window.currentTime, { cache: 'no-store' })
                        .then(r => r.json())
                        .then(data => {
                            const box  = document.getElementById('activeEventsList');
                            const list = Array.isArray(data) ? data : Object.values(data);

                            if (list.length === 0) {
                                box.innerHTML =
                                    '<p class="text-gray-500 dark:text-gray-400">Geen actieve events</p>';
                                return;
                            }

                            let html = '';
                            list.forEach(ev => {
                                if (ev.name?.includes('(Aangrenzend)')) return;

                                /* --- init diff --- */
                                let endSec = hmsToSec(ev.end_time);
                                let diff   = endSec - currentSimSec;
                                if (diff < 0) diff += SECS_DAY;

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

                            box.innerHTML = html;
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
