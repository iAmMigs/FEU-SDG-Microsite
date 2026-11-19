import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'backdrop', 'panel', 'title', 'description', 'image', 
        'actionPrompt', 'buttonsContainer', 'libraryBtn', 'activityBtn', 'footerContainer'
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
            if (this.hasFooterContainerTarget) {
                this.footerContainerTarget.style.display = 'flex';
            }
            if (this.hasActionPromptTarget) {
                this.actionPromptTarget.textContent = 'Which would you like to visit?';
                this.actionPromptTarget.style.display = 'block';
            }
            if (this.hasButtonsContainerTarget) {
                this.buttonsContainerTarget.style.display = 'flex';
            }
            
            // Use base URLs from data attributes or fallback to hardcoded paths
            const libraryBaseUrl = this.hasLibraryBtnTarget ? (this.libraryBtnTarget.dataset.baseUrl || '/library') : '/library';
            const activityBaseUrl = this.hasActivityBtnTarget ? (this.activityBtnTarget.dataset.baseUrl || '/news') : '/news';
            
            if (this.hasLibraryBtnTarget) {
                this.libraryBtnTarget.href = `${libraryBaseUrl}?goals[]=${goalNum}`; 
            }
            
            if (this.hasActivityBtnTarget) {
                this.activityBtnTarget.href = `${activityBaseUrl}?goals[]=${goalNum}`;
            }
        } else {
            if (this.hasActionPromptTarget) {
                this.actionPromptTarget.textContent = '';
                this.actionPromptTarget.style.display = 'none';
            }
            if (this.hasButtonsContainerTarget) {
                this.buttonsContainerTarget.style.display = 'none';
            }
            if (this.hasFooterContainerTarget) {
                this.footerContainerTarget.style.display = 'none';
            }
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