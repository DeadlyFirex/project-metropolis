import html2canvas from 'html2canvas';
import { jsPDF } from 'jspdf';

export async function downloadDashboardAsPDF() {
    console.log("Generating PDF...");
    const pdf = new jsPDF({ unit: 'px', format: 'a4' });
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();
    const padding = 40;
    const now = new Date().toLocaleString('nl-NL');

    let currentY = 60;
    let pageNumber = 1;

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

        const canvas = await html2canvas(element, { scale: 2 });
        const croppedCanvas = cropTopWhitespace(canvas, 40);

        const imgData = croppedCanvas.toDataURL('image/png');
        const ratio = croppedCanvas.width / croppedCanvas.height;
        const maxWidth = pageWidth - padding * 2;
        let imgWidth = maxWidth;
        let imgHeight = imgWidth / ratio;

        const lines = pdf.splitTextToSize(descriptions[i], maxWidth);
        const lineHeight = 14;
        const descriptionHeight = lines.length * lineHeight;

        const maxImgHeight = pageHeight - padding * 2 - descriptionHeight - 20;
        if (imgHeight > maxImgHeight) {
            imgHeight = maxImgHeight;
            imgWidth = imgHeight * ratio;
        }

        if (currentY + descriptionHeight + imgHeight > pageHeight - padding) {
            addFooter(pdf, pageWidth, pageHeight, pageNumber++);
            pdf.addPage();
            currentY = 60;
            addHeader(pdf, pageWidth, now);
        }

        if (pageNumber === 1 && currentY === 60) {
            addHeader(pdf, pageWidth, now);
        }

        pdf.setFont('helvetica', 'italic');
        pdf.setFontSize(12);
        pdf.text(lines, padding, currentY);
        currentY += descriptionHeight + 6;

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
    pdf.text(`Pagina ${pageNumber}`, pageWidth / 2, pageHeight - 10, { align: 'center' });
}

function cropTopWhitespace(canvas, cropHeight = 40) {
    const cropped = document.createElement('canvas');
    cropped.width = canvas.width;
    cropped.height = canvas.height - cropHeight;
    const ctx = cropped.getContext('2d');
    ctx.drawImage(canvas, 0, cropHeight, canvas.width, cropped.height, 0, 0, canvas.width, cropped.height);
    return cropped;
}
