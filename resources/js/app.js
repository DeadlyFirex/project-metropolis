import './bootstrap';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import './drag.js';
import './font-size.js';
import './effect-control.js';
import './effect-flash.js';
import './openModule.js';
import './feedback.js';
import './clock.js';

import { initLibrarySearch } from './library-search.js';
import { initFeedback } from './feedback';

document.addEventListener('DOMContentLoaded', () => {
    initLibrarySearch();
    initFeedback();
});
