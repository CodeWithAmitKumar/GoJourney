/**
 * GoJourney - Result Section Animations
 * Adds beautiful animations and interactive effects to search results
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add staggered animation to result cards when they appear
    animateResultCards();
    
    // Add interaction effects to various result elements
    addResultInteractions();
    
    // Add observer for new cards that might be added dynamically
    observeResultChanges();
    
    // Apply extra visibility enhancements
    enhanceResultVisibility();
});

/**
 * Animate result cards with staggered reveal
 */
function animateResultCards() {
    const resultCards = document.querySelectorAll('.result-card, .hotel-result-card');
    
    if (resultCards.length === 0) return;
    
    // Add initial state to all cards (invisible and translated down)
    resultCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(40px)';
        card.style.transition = `all 0.5s ease ${index * 0.1}s`;
    });
    
    // Force browser reflow to ensure the initial state is applied
    void resultCards[0].offsetWidth;
    
    // Reveal cards with staggered timing
    resultCards.forEach(card => {
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    });
    
    // Once animation is complete, reset the inline transform to allow hover effects
    setTimeout(() => {
        resultCards.forEach(card => {
            card.style.transform = '';
        });
    }, resultCards.length * 100 + 500);
}

/**
 * Add interaction effects to result elements
 */
function addResultInteractions() {
    // Pulsing effect for prices
    const prices = document.querySelectorAll('.price');
    prices.forEach(price => {
        price.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
            this.style.textShadow = '0 0 10px rgba(var(--primary-rgb), 0.3)';
        });
        
        price.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.textShadow = '';
        });
    });
    
    // Animated journey line
    const durationLines = document.querySelectorAll('.duration-line');
    durationLines.forEach(line => {
        line.addEventListener('mouseenter', function() {
            this.style.height = '5px';
            
            // Add animation for dots
            const beforeAfter = document.createElement('style');
            beforeAfter.textContent = `
                .duration-line:before, .duration-line:after {
                    animation: pulseDot 1.5s infinite;
                }
                @keyframes pulseDot {
                    0% { transform: translateY(-50%) scale(1); }
                    50% { transform: translateY(-50%) scale(1.3); }
                    100% { transform: translateY(-50%) scale(1); }
                }
            `;
            document.head.appendChild(beforeAfter);
            line.setAttribute('data-style-id', document.styleSheets.length - 1);
        });
        
        line.addEventListener('mouseleave', function() {
            this.style.height = '';
            
            // Remove the animation style
            const styleId = this.getAttribute('data-style-id');
            if (styleId && document.styleSheets[styleId]) {
                document.head.removeChild(document.styleSheets[styleId].ownerNode);
            }
        });
    });
    
    // Shimmer effect for Book Now buttons
    const bookButtons = document.querySelectorAll('.book-now-btn');
    bookButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.querySelector(':before')?.style.left = '100%';
        });
    });
    
    // Subtle effect for availability badges
    const availabilityBadges = document.querySelectorAll('.availability');
    availabilityBadges.forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.1)';
        });
        
        badge.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
    
    // Animated headers
    animateResultHeaders();
}

/**
 * Animate the results header with a swipe-in effect
 */
function animateResultHeaders() {
    const headers = document.querySelectorAll('.results-header');
    
    headers.forEach(header => {
        // Skip if already animated
        if (header.classList.contains('animated')) return;
        
        // Add initial state
        header.style.opacity = '0';
        header.style.transform = 'translateX(-20px)';
        header.style.transition = 'all 0.5s ease';
        
        // Force reflow
        void header.offsetWidth;
        
        // Animate in
        header.style.opacity = '1';
        header.style.transform = 'translateX(0)';
        
        // Add animated class to prevent re-animation
        header.classList.add('animated');
        
        // Animate icon
        const icon = header.querySelector('h3 i');
        if (icon) {
            icon.style.transform = 'scale(0)';
            icon.style.transition = 'transform 0.3s ease 0.3s';
            
            setTimeout(() => {
                icon.style.transform = 'scale(1)';
            }, 300);
        }
        
        // Animate counter
        const counter = header.querySelector('.results-count');
        if (counter) {
            counter.style.opacity = '0';
            counter.style.transform = 'translateX(20px)';
            counter.style.transition = 'all 0.4s ease 0.4s';
            
            setTimeout(() => {
                counter.style.opacity = '1';
                counter.style.transform = 'translateX(0)';
            }, 400);
        }
    });
}

/**
 * Set up observer to detect new results that might be added dynamically
 */
function observeResultChanges() {
    // Select the container where results might be added
    const resultsContainer = document.querySelector('.results-container');
    if (!resultsContainer) return;
    
    // Create a mutation observer
    const observer = new MutationObserver(mutations => {
        // Check if result cards were added
        const hasNewCards = mutations.some(mutation => 
            Array.from(mutation.addedNodes).some(node => 
                node.classList && 
                (node.classList.contains('result-card') || 
                node.classList.contains('hotel-result-card') ||
                node.querySelector('.result-card, .hotel-result-card'))
            )
        );
        
        if (hasNewCards) {
            // Run animations for new cards
            animateResultCards();
            addResultInteractions();
        }
        
        // Check if a results header was added
        const hasNewHeader = mutations.some(mutation => 
            Array.from(mutation.addedNodes).some(node => 
                node.classList && 
                (node.classList.contains('results-header') || 
                node.querySelector('.results-header'))
            )
        );
        
        if (hasNewHeader) {
            animateResultHeaders();
        }
    });
    
    // Start observing
    observer.observe(resultsContainer, { 
        childList: true, 
        subtree: true 
    });
}

/**
 * Add decorative dots to journey duration lines
 */
function enhanceJourneyTimeline() {
    const durationLines = document.querySelectorAll('.duration-line');
    
    durationLines.forEach(line => {
        // Create multiple dots along the line for a more decorative timeline
        const lineWidth = line.offsetWidth;
        const numDots = Math.floor(lineWidth / 30); // Create a dot every ~30px
        
        for (let i = 1; i < numDots; i++) {
            const dot = document.createElement('span');
            dot.className = 'timeline-dot';
            dot.style.position = 'absolute';
            dot.style.width = '6px';
            dot.style.height = '6px';
            dot.style.backgroundColor = 'rgba(var(--primary-rgb), 0.5)';
            dot.style.borderRadius = '50%';
            dot.style.top = '50%';
            dot.style.left = `${(i * 100) / numDots}%`;
            dot.style.transform = 'translate(-50%, -50%)';
            
            line.appendChild(dot);
        }
    });
}

/**
 * Enhance visibility of result cards by applying additional styling
 */
function enhanceResultVisibility() {
    // Ensure high opacity for result cards
    const resultCards = document.querySelectorAll('.result-card, .hotel-result-card');
    resultCards.forEach(card => {
        // Add extra background for opacity
        card.style.backgroundColor = '#ffffff';
        
        // Add extra border for better definition
        card.style.border = '1px solid rgba(0, 0, 0, 0.1)';
        
        // Ensure good shadow
        card.style.boxShadow = '0 8px 20px rgba(0, 0, 0, 0.1)';
    });
    
    // Make sure journey times are clearly visible
    const journeyElements = document.querySelectorAll('.journey-times, .journey-date, .price-availability, .booking-action');
    journeyElements.forEach(el => {
        el.style.backgroundColor = '#ffffff';
    });
    
    // Enhance result text visibility
    const textElements = document.querySelectorAll('.train-name, .airline-name, .hotel-name, .time, .station, .airport');
    textElements.forEach(el => {
        el.style.textShadow = '0 1px 1px rgba(255, 255, 255, 0.8)';
    });
    
    // Add timeline dots for enhanced journey visualization
    enhanceJourneyTimeline();
} 