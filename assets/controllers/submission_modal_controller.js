import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['modal'];

    show(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        console.log('Showing submission modal');
        this.modalTarget.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    close(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        console.log('Closing submission modal');
        this.modalTarget.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}
