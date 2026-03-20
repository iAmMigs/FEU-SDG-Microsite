import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'backdrop', 'panel', 'title', 'description', 'image', 
        'actionPrompt', 'buttonsContainer', 'libraryBtn', 'activityBtn'
    ];

    open(event) {
        const button = event.currentTarget;
        const goalNum = button.dataset.goal;
        const isActive = button.dataset.active === 'true';

        // 1. Populate basic data
        this.titleTarget.textContent = button.dataset.title;
        this.descriptionTarget.textContent = button.dataset.description;
        
        // 2. Set the colored Web Goal image
        this.imageTarget.src = button.dataset.image;
        this.imageTarget.alt = `SDG ${goalNum} Web Goal`;

        // 3. Handle Active vs Inactive State
        if (isActive) {
            this.actionPromptTarget.textContent = 'Which would you like to visit?';
            this.buttonsContainerTarget.style.display = 'flex';
            
            // --- THE REDIRECT LOGIC ---
            // If your library route is /library or /thesis, make sure this matches
            this.libraryBtnTarget.href = `/library?goals[]=${goalNum}`; 
            
            // This safely directs to the News Controller and applies the checkbox filter
            this.activityBtnTarget.href = `/news?goals[]=${goalNum}`;
        } else {
            this.actionPromptTarget.textContent = 'SDG is currently not actively focused.';
            this.buttonsContainerTarget.style.display = 'none';
        }

        // 4. Animate modal in
        this.backdropTarget.classList.remove('hidden');
        setTimeout(() => {
            this.backdropTarget.classList.remove('opacity-0');
            this.backdropTarget.classList.add('opacity-100');
            this.panelTarget.classList.remove('opacity-0', 'scale-95', 'translate-y-4');
            this.panelTarget.classList.add('opacity-100', 'scale-100', 'translate-y-0');
        }, 10);
    }

    close() {
        // Animate modal out
        this.backdropTarget.classList.remove('opacity-100');
        this.backdropTarget.classList.add('opacity-0');
        this.panelTarget.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
        this.panelTarget.classList.add('opacity-0', 'scale-95', 'translate-y-4');
        setTimeout(() => {
            this.backdropTarget.classList.add('hidden');
        }, 300);
    }
}