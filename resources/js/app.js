import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import './dashboard.js';
import './font-size.js';
import './effect-control.js';
import './effect-flash.js';
import './openModule.js';
import './services/feedback.js';

import { initLibrarySearch } from './services/library.js';
import { initFeedback } from './services/feedback.js';

document.addEventListener('DOMContentLoaded', () => {
    initLibrarySearch();
    initFeedback();
});
