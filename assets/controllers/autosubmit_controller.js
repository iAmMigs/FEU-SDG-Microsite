import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    // Variable to hold our timer
    timeout = null;

    submit() {
        // 1. Immediately swap to the animated skeleton loader
        const loader = document.getElementById('theses-skeleton');
        const results = document.getElementById('theses-results');
        
        if (loader && results) {
            results.classList.add('hidden');
            loader.classList.remove('hidden');
        }

        // 2. Clear the previous timer if the user clicks/types again quickly
        if (this.timeout) {
            clearTimeout(this.timeout);
        }

        // 3. Set a 0.5 second (500ms) delay before actually submitting to the server
        this.timeout = setTimeout(() => {
            this.element.requestSubmit();
        }, 500);
    }
}