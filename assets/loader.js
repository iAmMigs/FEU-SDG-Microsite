export function initLoader() {
    let loadStartTime = 0;
    const MIN_DISPLAY_TIME = 500; // Minimum time in milliseconds (0.5 seconds)

    const hideLoader = () => {
        const elapsedTime = Date.now() - loadStartTime;
        const timeToWait = Math.max(0, MIN_DISPLAY_TIME - elapsedTime);

        setTimeout(() => {
            // Find the loader currently in the DOM right now
            const loader = document.getElementById('page-loader');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 500); // Matches the Tailwind duration-500
            }
        }, timeToWait);
    };

    const showLoader = () => {
        loadStartTime = Date.now();
        // Find the loader currently in the DOM right now
        const loader = document.getElementById('page-loader');
        if (loader) {
            loader.style.display = 'flex';
            setTimeout(() => {
                const activeLoader = document.getElementById('page-loader');
                if (activeLoader) activeLoader.style.opacity = '1';
            }, 10);
        }
    };

    // 1. Hide on standard hard page load
    window.addEventListener('load', () => {
        loadStartTime = Date.now(); // Reset timer for initial page load
        hideLoader();
    });

    // 2. Show when Turbo starts fetching a new page
    document.addEventListener('turbo:visit', showLoader);
    
    // 3. Hide when Turbo finishes swapping the body
    document.addEventListener('turbo:load', hideLoader);
    
    // 4. Fallback for cached back/forward navigation
    document.addEventListener('turbo:render', hideLoader);
}