import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['form', 'page'];

    connect() {
        this.timeout = null;
    }

    submit(event) {
        // Clear existing timeout to prevent server spam on every keystroke
        clearTimeout(this.timeout);

        this.timeout = setTimeout(() => {
            // Reset to page 1 ONLY IF they are changing a search filter (not clicking next page)
            if (event.target.name !== 'page' && this.hasPageTarget) {
                this.pageTarget.value = 1;
            }

            // CRITICAL FIX: requestSubmit() forces Turbo to intercept and update the frame.
            // Using standard .submit() will break the Turbo Frame integration.
            if (typeof this.formTarget.requestSubmit === 'function') {
                this.formTarget.requestSubmit();
            } else {
                this.formTarget.submit();
            }
        }, 300); // 300ms delay
    }

    prevPage(event) {
        event.preventDefault();
        if (this.hasPageTarget) {
            let current = parseInt(this.pageTarget.value) || 1;
            if (current > 1) {
                this.pageTarget.value = current - 1;
                this.submit({ target: this.pageTarget });
            }
        }
    }

    nextPage(event) {
        event.preventDefault();
        if (this.hasPageTarget) {
            let current = parseInt(this.pageTarget.value) || 1;
            let max = parseInt(this.pageTarget.dataset.max) || 1;
            if (current < max) {
                this.pageTarget.value = current + 1;
                this.submit({ target: this.pageTarget });
            }
        }
    }
}