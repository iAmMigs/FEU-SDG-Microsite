import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    // 1. Add 'link' to the targets array
    static targets = ['backdrop', 'panel', 'title', 'description', 'image', 'badge', 'link'];

    open(event) {
        const button = event.currentTarget;
        const goalNum = button.dataset.goal; // Extract the goal number to reuse
        
        // Populate the modal with data from the clicked card
        this.titleTarget.textContent = button.dataset.title;
        this.descriptionTarget.textContent = button.dataset.description;
        this.imageTarget.src = button.dataset.image;
        this.badgeTarget.textContent = `SDG ${goalNum}`;

        // 2. Update the "View Related Projects" link URL dynamically
        if (this.hasLinkTarget) {
            this.linkTarget.href = `/library?goals[]=${goalNum}`;
        }

        // 3. Unhide the backdrop container
        this.backdropTarget.classList.remove('hidden');
        
        // 4. Small delay to allow the browser to register the 'display: block' before animating
        setTimeout(() => {
            // Fade in the background blur
            this.backdropTarget.classList.remove('opacity-0');
            this.backdropTarget.classList.add('opacity-100');
            
            // Pop in the modal panel
            this.panelTarget.classList.remove('opacity-0', 'scale-95', 'translate-y-4');
            this.panelTarget.classList.add('opacity-100', 'scale-100', 'translate-y-0');
        }, 10);
    }

    close() {
        // 1. Start the exit animations
        this.backdropTarget.classList.remove('opacity-100');
        this.backdropTarget.classList.add('opacity-0');
        
        this.panelTarget.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
        this.panelTarget.classList.add('opacity-0', 'scale-95', 'translate-y-4');
        
        // 2. Hide the container completely after the transition finishes (300ms)
        setTimeout(() => {
            this.backdropTarget.classList.add('hidden');
        }, 300);
    }
}