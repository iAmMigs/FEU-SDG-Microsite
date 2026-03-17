import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['menu', 'openIcon', 'closeIcon'];

    toggle() {
        // Toggle the visibility of the mobile dropdown menu
        this.menuTarget.classList.toggle('hidden');
        
        // Swap the Hamburger icon and the X (close) icon
        this.openIconTarget.classList.toggle('hidden');
        this.openIconTarget.classList.toggle('block');
        
        this.closeIconTarget.classList.toggle('hidden');
        this.closeIconTarget.classList.toggle('block');
    }
}