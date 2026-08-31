import './add-to-homescreen';
import mergeSearch from './merge-search';

// Set up for Alpine.js
document.addEventListener('alpine:init', () => {
    Alpine.data('mergeSearch', mergeSearch);
});
