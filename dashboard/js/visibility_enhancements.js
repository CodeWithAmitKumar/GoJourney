/**
 * Visibility Enhancements for GoJourney
 * This script improves UI visibility with dynamic adjustments for search results only
 */

document.addEventListener('DOMContentLoaded', function() {
    // Apply enhanced contrast to key elements
    enhanceResultsVisibility();
    
    // Set up event listeners for visibility toggles
    setupVisibilityControls();
    
    // Add high contrast toggle
    addHighContrastToggle();
});

/**
 * Enhances result cards visibility with targeted adjustments
 */
function enhanceResultsVisibility() {
    // Add stronger box-shadow to result cards on hover for better focus
    const resultCards = document.querySelectorAll('.result-card, .hotel-result-card');
    resultCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.15)';
            this.style.borderColor = 'rgba(var(--primary-rgb), 0.3)';
            this.style.transform = 'translateY(-3px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.boxShadow = '';
            this.style.borderColor = '';
            this.style.transform = '';
        });
    });

    // Enhance color contrast for prices
    const priceElements = document.querySelectorAll('.price');
    priceElements.forEach(price => {
        price.style.textShadow = '0 1px 1px rgba(255, 255, 255, 0.3)';
    });
    
    // Apply better visual focus for action buttons inside result cards
    const actionButtons = document.querySelectorAll('.book-now-btn, .modify-search-btn, .show-details-btn, .view-details-btn');
    actionButtons.forEach(button => {
        button.style.fontWeight = '700';
        
        button.addEventListener('mouseenter', function() {
            if (this.classList.contains('book-now-btn')) {
                this.style.transform = 'translateY(-3px)';
                this.style.boxShadow = '0 6px 15px rgba(0, 0, 0, 0.2)';
            } else if (this.classList.contains('show-details-btn') || this.classList.contains('view-details-btn')) {
                this.style.backgroundColor = 'rgba(var(--primary-rgb), 0.15)';
            }
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
            if (this.classList.contains('show-details-btn') || this.classList.contains('view-details-btn')) {
                this.style.backgroundColor = '';
            }
        });
    });
    
    // Improve visibility of result card details 
    const timeElements = document.querySelectorAll('.time');
    timeElements.forEach(time => {
        time.style.textShadow = '0 1px 1px rgba(255, 255, 255, 0.3)';
    });
    
    const stationElements = document.querySelectorAll('.station, .airport');
    stationElements.forEach(station => {
        station.style.fontWeight = '600';
    });
    
    // Enhance availability badges
    const availabilityElements = document.querySelectorAll('.availability');
    availabilityElements.forEach(availability => {
        availability.style.boxShadow = '0 2px 5px rgba(0, 0, 0, 0.1)';
    });
    
    // Improve room options in hotel cards
    const roomOptions = document.querySelectorAll('.room-option');
    roomOptions.forEach(room => {
        room.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(var(--primary-rgb), 0.08)';
            this.style.transform = 'scale(1.01)';
        });
        
        room.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
            this.style.transform = '';
        });
    });
}

/**
 * Set up controls for additional visibility adjustments
 */
function setupVisibilityControls() {
    // Add text size adjustment option to footer if it doesn't exist
    if (!document.getElementById('text-size-controls')) {
        const resultsContainer = document.querySelector('.results-page-container');
        if (resultsContainer) {
            const textSizeControls = document.createElement('div');
            textSizeControls.id = 'text-size-controls';
            textSizeControls.className = 'text-size-controls';
            textSizeControls.innerHTML = `
                <span>Text Size: </span>
                <button class="text-size-btn" data-size="small">A-</button>
                <button class="text-size-btn active" data-size="medium">A</button>
                <button class="text-size-btn" data-size="large">A+</button>
            `;
            
            // Insert before the search results
            const searchResults = resultsContainer.querySelector('.results-container');
            if (searchResults) {
                resultsContainer.insertBefore(textSizeControls, searchResults);
            }
            
            // Add styles for text size controls
            const style = document.createElement('style');
            style.textContent = `
                .text-size-controls {
                    display: flex;
                    align-items: center;
                    margin-bottom: 20px;
                    padding: 10px 15px;
                    background-color: rgba(var(--primary-rgb), 0.1);
                    border-radius: 8px;
                    justify-content: flex-end;
                }
                .text-size-controls span {
                    margin-right: 10px;
                    font-weight: 600;
                }
                .text-size-btn {
                    background: none;
                    border: 1px solid rgba(var(--primary-rgb), 0.3);
                    border-radius: 4px;
                    padding: 5px 10px;
                    margin: 0 5px;
                    cursor: pointer;
                    transition: all 0.2s;
                }
                .text-size-btn.active {
                    background-color: var(--primary-color);
                    color: white;
                }
                .text-size-btn:hover:not(.active) {
                    background-color: rgba(var(--primary-rgb), 0.1);
                }
            `;
            document.head.appendChild(style);
            
            // Add event listeners for text size buttons
            const textSizeButtons = document.querySelectorAll('.text-size-btn');
            textSizeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const size = this.getAttribute('data-size');
                    changeTextSize(size);
                    
                    // Update active button
                    textSizeButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        }
    }
}

/**
 * Change text size for search results
 * @param {string} size - small, medium, or large
 */
function changeTextSize(size) {
    const resultsContainer = document.querySelector('.results-page-container');
    
    if (resultsContainer) {
        switch(size) {
            case 'small':
                resultsContainer.style.fontSize = '0.9rem';
                break;
            case 'medium':
                resultsContainer.style.fontSize = '1rem';
                break;
            case 'large':
                resultsContainer.style.fontSize = '1.1rem';
                break;
        }
    }
    
    // Store preference in localStorage
    localStorage.setItem('gojourney-text-size', size);
}

// Check for stored text size preference and apply on load
const storedTextSize = localStorage.getItem('gojourney-text-size');
if (storedTextSize) {
    changeTextSize(storedTextSize);
    
    // Update active button if controls exist
    setTimeout(() => {
        const textSizeButtons = document.querySelectorAll('.text-size-btn');
        textSizeButtons.forEach(button => {
            if (button.getAttribute('data-size') === storedTextSize) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        });
    }, 100);
}

/**
 * Adds a high contrast mode toggle button
 */
function addHighContrastToggle() {
    // Create the high contrast link element if it doesn't exist
    if (!document.getElementById('high-contrast-stylesheet')) {
        const linkEl = document.createElement('link');
        linkEl.id = 'high-contrast-stylesheet';
        linkEl.rel = 'stylesheet';
        linkEl.href = 'css/high_contrast.css';
        linkEl.disabled = true; // Start with it disabled
        document.head.appendChild(linkEl);
    }
    
    // Add toggle button
    const toggleBtn = document.createElement('button');
    toggleBtn.className = 'high-contrast-toggle';
    toggleBtn.innerHTML = '<i class="fas fa-adjust"></i>';
    toggleBtn.setAttribute('title', 'Toggle High Contrast');
    document.body.appendChild(toggleBtn);
    
    // Check local storage for user preference
    const highContrastEnabled = localStorage.getItem('gojourney-high-contrast') === 'true';
    if (highContrastEnabled) {
        document.getElementById('high-contrast-stylesheet').disabled = false;
        toggleBtn.classList.add('active');
    }
    
    // Add click event listener
    toggleBtn.addEventListener('click', function() {
        const stylesheet = document.getElementById('high-contrast-stylesheet');
        const currentState = !stylesheet.disabled;
        
        // Toggle stylesheet
        stylesheet.disabled = currentState;
        
        // Save preference
        localStorage.setItem('gojourney-high-contrast', (!currentState).toString());
        
        // Toggle active class
        this.classList.toggle('active');
    });
} 