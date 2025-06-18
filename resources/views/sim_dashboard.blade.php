<x-app-layout>
    <x-slot name="header">
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
                        @include('components.calculated-effects', ['slots' => $slots])
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


            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

            <script>
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

                function updateActiveEvents() {
                    fetch('/events/slot-events')
                        .then(response => response.json())
                        .then(data => {
                            const eventsList = document.getElementById('activeEventsList');
                            const noEventsMessage = document.getElementById('noEventsMessage');

                            if (Object.keys(data).length === 0) {
                                eventsList.innerHTML =
                                    '<p class="text-gray-500 dark:text-gray-400" id="noEventsMessage">Geen actieve events</p>';
                            } else {
                                let eventsHtml = '';
                                for (const [slotId, event] of Object.entries(data)) {
                                    if (event.event_name && event.event_name.includes('(Aangrenzend)')) {
                                        continue;
                                    }
                                    eventsHtml += `
                                <div class="flex items-center justify-between p-3 mb-2 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg">
                                    <div class="flex items-center space-x-4">
                                        <div class="bg-yellow-500 text-white px-2 py-1 rounded text-sm font-medium">
                                            Vakje ${slotId}
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-800 dark:text-gray-200">${event.event_name}</span>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                Nog ${event.time_remaining} resterend
                                                ${event.is_recurring ? '<span class="ml-2 bg-blue-200 text-blue-800 px-2 py-1 rounded text-xs">Terugkerend</span>' : ''}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse" title="Actief"></div>
                                        <a href="/events" class="text-blue-600 hover:text-blue-800 text-sm">
                                            Beheren
                                        </a>
                                    </div>
                                </div>
                            `;
                                }
                                eventsList.innerHTML = eventsHtml;
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching events:', error);
                        });
                }

                document.addEventListener('DOMContentLoaded', function() {
                    updateActiveEvents();
                    setInterval(updateActiveEvents, 30000);
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
