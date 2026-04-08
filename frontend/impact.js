// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.counter');
    let animated = false; // Prevent multiple animations
    
    // Function to check if element is in viewport
    function isInViewport(element) {
        const rect = element.getBoundingClientRect();
        const elementTop = rect.top;
        const elementBottom = rect.bottom;
        
        // Element is visible if any part is in viewport
        return (elementTop < window.innerHeight && elementBottom > 0);
    }
    
    // Function to check if impact section is visible
    function checkImpactVisibility() {
        const impactSection = document.getElementById('impact');
        
        if (!animated && impactSection && isInViewport(impactSection)) {
            animated = true;
            startCounters();
        }
    }
    
    // Function to animate counters
    function startCounters() {
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            let current = 0;
            const steps = 30; // Number of increments
            const increment = target / steps;
            let step = 0;
            
            const updateCounter = () => {
                step++;
                current += increment;
                if (step < steps) {
                    counter.innerText = Math.ceil(current);
                    setTimeout(updateCounter, 15); // ~0.45 seconds total
                } else {
                    counter.innerText = target;
                }
            };
            
            updateCounter();
        });
    }
    
    // Listen for scroll events
    window.addEventListener('scroll', checkImpactVisibility);
    
    // Also check on initial load
    setTimeout(checkImpactVisibility, 100);
});
