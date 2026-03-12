import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['slide'];

    connect() {
        // 1. Shuffle the images randomly when the controller connects
        this.slides = this.shuffleArray([...this.slideTargets]);
        this.index = 0;
        
        // 2. Hide all slides initially
        this.slideTargets.forEach(slide => {
            slide.classList.remove('opacity-100');
            slide.classList.add('opacity-0');
        });
        
        // 3. Reveal the first random slide
        if (this.slides.length > 0) {
            this.slides[0].classList.replace('opacity-0', 'opacity-100');
        }

        // 4. Start the timer (changes every 5 seconds)
        this.interval = setInterval(() => {
            this.next();
        }, 5000);
    }

    disconnect() {
        // Automatically clears the timer when you navigate to another page
        clearInterval(this.interval);
    }

    next() {
        if (this.slides.length === 0) return;
        
        // Fade out current slide
        this.slides[this.index].classList.replace('opacity-100', 'opacity-0');
        
        // Calculate next index, looping back to 0 if at the end
        this.index = (this.index + 1) % this.slides.length;
        
        // Fade in next slide
        this.slides[this.index].classList.replace('opacity-0', 'opacity-100');
    }

    // Fisher-Yates shuffle algorithm
    shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }
}