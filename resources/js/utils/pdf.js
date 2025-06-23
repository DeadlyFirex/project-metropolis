import html2canvas from 'html2canvas';
import { jsPDF } from 'jspdf';

/**
 * Captures selected dashboard sections and generates a PDF report.
 * Includes headers, footers, and descriptions for each section.
 */
export async function downloadDashboardAsPDF() {
    console.log("Generating PDF...");
    const pdf = new jsPDF({ unit: 'px', format: 'a4' });
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();
    const padding = 40;
    const now = new Date().toLocaleString('nl-NL');

    let currentY = 60;
    let pageNumber = 1;

    // Define which sections to include in the PDF
    const targets = [
        document.getElementById('city-grid'),
        document.getElementById('effect-view'),
    ];

    const descriptions = [
        "Overzicht van de stadsgrid met alle modules op dit moment.",
        "Effectenweergave van de simulatie op dit moment.",
    ];

    for (let i = 0; i < targets.length; i++) {
        const element = targets[i];
        if (!element) continue;

        // Capture the element as canvas, crop unnecessary top space
        const canvas = await html2canvas(element, { scale: 2 });
        const croppedCanvas = cropTopWhitespace(canvas, 40);

        const imgData = croppedCanvas.toDataURL('image/png');
        const ratio = croppedCanvas.width / croppedCanvas.height;
        const maxWidth = pageWidth - padding * 2;
        let imgWidth = maxWidth;
        let imgHeight = imgWidth / ratio;

        // Calculate description block height
        const lines = pdf.splitTextToSize(descriptions[i], maxWidth);
        const lineHeight = 14;
        const descriptionHeight = lines.length * lineHeight;

        // Ensure image fits within page bounds
        const maxImgHeight = pageHeight - padding * 2 - descriptionHeight - 20;
        if (imgHeight > maxImgHeight) {
            imgHeight = maxImgHeight;
            imgWidth = imgHeight * ratio;
        }

        // Start new page if needed
        if (currentY + descriptionHeight + imgHeight > pageHeight - padding) {
            addFooter(pdf, pageWidth, pageHeight, pageNumber++);
            pdf.addPage();
            currentY = 60;
            addHeader(pdf, pageWidth, now);
        }

        // Add header on first page
        if (pageNumber === 1 && currentY === 60) {
            addHeader(pdf, pageWidth, now);
        }

        // Draw description text
        pdf.setFont('helvetica', 'italic');
        pdf.setFontSize(12);
        pdf.text(lines, padding, currentY);
        currentY += descriptionHeight + 6;

        // Draw captured image
        pdf.addImage(imgData, 'PNG', (pageWidth - imgWidth) / 2, currentY, imgWidth, imgHeight);
        currentY += imgHeight + 20;
    }

    // Finalize PDF with footer and save
    addFooter(pdf, pageWidth, pageHeight, pageNumber);
    pdf.save('simulatie-grid-en-effecten.pdf');
}

/**
 * Adds a header to the current page with title and timestamp.
 */
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

/**
 * Adds a footer to the current page with the page number.
 */
function addFooter(pdf, pageWidth, pageHeight, pageNumber) {
    pdf.setFontSize(9);
    pdf.setTextColor(150);
    pdf.text(`Pagina ${pageNumber}`, pageWidth / 2, pageHeight - 10, { align: 'center' });
}

/**
 * Crops white space from the top of a canvas.
 * Used to remove unnecessary margins before adding to PDF.
 */
function cropTopWhitespace(canvas, cropHeight = 40) {
    const cropped = document.createElement('canvas');
    cropped.width = canvas.width;
    cropped.height = canvas.height - cropHeight;
    const ctx = cropped.getContext('2d');
    ctx.drawImage(canvas, 0, cropHeight, canvas.width, cropped.height, 0, 0, canvas.width, cropped.height);
    return cropped;
}
