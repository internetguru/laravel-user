import './add-to-homescreen';
import mergeCombobox from './merge-combobox';

// Set up for Alpine.js
document.addEventListener('alpine:init', () => {
    Alpine.data('mergeCombobox', mergeCombobox);
});
