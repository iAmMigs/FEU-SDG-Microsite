import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['form', 'page', 'dateFilter', 'startDate', 'endDate', 'dateRangeField'];

    connect() {
        this.timeout = null;
        // Initialize visibility on connect (handles Turbo navigation and page reloads)
        this.toggleDateRange();
    }

    toggleDateRange() {
        if (!this.hasDateFilterTarget) return;

        const isDateRange = this.dateFilterTarget.value === 'date_range';
        
        // Toggle visibility for any field marked as a dateRangeField target
        this.dateRangeFieldTargets.forEach(el => {
            el.style.display = isDateRange ? 'block' : 'none';
        });

        // Submit for all options EXCEPT Date Range (which requires From/To)
        if (!isDateRange) {
            this.submit();
        }
    }

    submit(event) {
        // If "Date Range" is selected, only submit if BOTH dates are filled
        if (this.hasDateFilterTarget && this.dateFilterTarget.value === 'date_range') {
            if (!this.startDateTarget.value || !this.endDateTarget.value) {
                return;
            }
        }

        // Clear existing timeout to prevent server spam on every keystroke
        clearTimeout(this.timeout);

        this.timeout = setTimeout(() => {
            // Reset to page 1 ONLY IF they are changing a search filter (not clicking next page)
            if (event && event.target && event.target.name !== 'page' && this.hasPageTarget) {
                this.pageTarget.value = 1;
            }

            // CRITICAL FIX: requestSubmit() forces Turbo to intercept and update the frame.
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