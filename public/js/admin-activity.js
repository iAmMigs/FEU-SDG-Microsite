document.addEventListener('DOMContentLoaded', () => {
    
    // We use event delegation to catch the changes instantly
    document.body.addEventListener('change', function(e) {
        if (!e.target || !e.target.name) return;

        // SCENARIO 1: Admin types into the Scheduled Release (publishAt)
        if (e.target.name.includes('[publishAt]')) {
            const activeSwitch = document.querySelector('input[type="checkbox"][name$="[isActive]"]');
            // If there is a date entered, force the switch OFF
            if (activeSwitch && e.target.value !== '') {
                activeSwitch.checked = false;
            }
        }

        // SCENARIO 2: Admin clicks the Active Switch (isActive)
        if (e.target.name.includes('[isActive]')) {
            const publishInput = document.querySelector('input[name$="[publishAt]"]');
            // If the switch is turned ON, force clear the date
            if (publishInput && e.target.checked) {
                publishInput.value = '';
            }
        }
    });
});