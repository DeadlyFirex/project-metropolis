// Grab relevant elements
const searchInput = document.getElementById('search');
const suggestionBox = document.getElementById('search-suggestions');
const cards = document.querySelectorAll('.module-card');
const emptyMsg = document.getElementById('no-matches-message');

/**
 * Initializes the module library search functionality.
 * - Applies an initial filter if the input has content.
 * - Adds an input listener to filter modules as the user types.
 * - Hides the suggestion box when clicking outside of it.
 */
export function initLibrarySearch() {
    filterModules();

    if (searchInput) {
        searchInput.addEventListener('input', filterModules);
    }

    document.addEventListener('click', (e) => {
        const suggestionBox = document.getElementById('suggestionBox');
        const searchInput = document.getElementById('searchInput');

        // Hide suggestion box if clicking outside of it
        if (suggestionBox && searchInput) {
            if (!suggestionBox.contains(e.target) && e.target !== searchInput) {
                suggestionBox.classList.add('hidden');
            }
        }
    });
}

/**
 * Filters visible module cards based on the user's input.
 * - Hides cards that don’t match the search term.
 * - Displays autocomplete suggestions based on module name or category.
 * - Shows a 'no matches' message if nothing is visible.
 */
function filterModules() {
    const query = searchInput.value.trim().toLowerCase();
    suggestionBox.innerHTML = '';
    let visibleCount = 0;
    const suggestions = new Set();

    cards.forEach(card => {
        const name = (card.dataset.name || '').toLowerCase();
        const category = (card.dataset.category || '').toLowerCase();
        const match = name.startsWith(query) || category.startsWith(query);

        // Show or hide the card
        card.style.display = match ? 'flex' : 'none';

        // Collect unique suggestions
        if (match) {
            visibleCount++;
            if (name.startsWith(query)) suggestions.add(card.dataset.name);
            else if (category.startsWith(query)) suggestions.add(card.dataset.category);
        }
    });

    // Show suggestion list
    if (query && suggestions.size > 0) {
        suggestionBox.classList.remove('hidden');
        suggestions.forEach(text => {
            const li = document.createElement('li');
            li.textContent = text;
            li.className = 'px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm';
            li.onclick = () => {
                searchInput.value = text;
                suggestionBox.classList.add('hidden');
                filterModules();
            };
            suggestionBox.appendChild(li);
        });
    } else {
        suggestionBox.classList.add('hidden');
    }

    // Show 'no results' message if applicable
    if (emptyMsg) {
        emptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}
