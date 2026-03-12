import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    // We target the form itself, and the page input box
    static targets = ['form', 'page'];
    timeout = null;

    submit(event) {
        // If the user changed a search filter (not the page box itself), reset page to 1
        if (event && event.target && event.target.name !== 'page' && this.hasPageTarget) {
            this.pageTarget.value = 1;
        }

        const loader = document.getElementById('theses-skeleton');
        const results = document.getElementById('theses-results');
        
        if (loader && results) {
            results.classList.add('hidden');
            loader.classList.remove('hidden');
        }

        if (this.timeout) {
            clearTimeout(this.timeout);
        }

        this.timeout = setTimeout(() => {
            this.formTarget.requestSubmit();
        }, 500);
    }

    // Handles the "<" arrow click
    prevPage(event) {
        event.preventDefault();
        let currentPage = parseInt(this.pageTarget.value) || 1;
        if (currentPage > 1) {
            this.pageTarget.value = currentPage - 1;
            this.submit(null);
        }
    }

    // Handles the ">" arrow click
    nextPage(event) {
        event.preventDefault();
        let currentPage = parseInt(this.pageTarget.value) || 1;
        let maxPage = parseInt(this.pageTarget.dataset.max) || 1;
        if (currentPage < maxPage) {
            this.pageTarget.value = currentPage + 1;
            this.submit(null);
        }
    }
}