function setFontSize(fontSize) {
    localStorage.setItem('fontSize', fontSize);
    applyFontSizeToTextElements(fontSize);
}

function applyFontSizeToTextElements(fontSize) {
    const allTextElements = document.querySelectorAll('body *');

    allTextElements.forEach(el => {
        if (el.children.length === 0 && el.textContent.trim() !== '') {
            el.style.fontSize = fontSize + 'em';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const increaseBtn = document.getElementById('increaseFontBtn');
    if (!increaseBtn) return;

    let normalSize = 1;
    let increasedSize = 1.7; // Change this to whatever step you prefer

    // Get stored font size or default
    let currentFontSize = parseFloat(localStorage.getItem('fontSize')) || normalSize;
    applyFontSizeToTextElements(currentFontSize);

    // Update button text based on current size
    if (currentFontSize > normalSize) {
        increaseBtn.textContent = 'Decrease Font Size';
    } else {
        increaseBtn.textContent = 'Increase Font Size';
    }

    increaseBtn.addEventListener('click', () => {
        if (currentFontSize === normalSize) {
            currentFontSize = increasedSize;
            increaseBtn.textContent = 'Decrease Font Size';
        } else {
            currentFontSize = normalSize;
            increaseBtn.textContent = 'Increase Font Size';
        }

        setFontSize(currentFontSize);
    });
});
