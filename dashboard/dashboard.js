// Theme toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded event handler running');
    
    // First ensure the wishlist table exists
    ensureWishlistTable();
    
    const themeToggle = document.getElementById('theme-toggle');
    const moonIcon = themeToggle.querySelector('.fa-moon');
    const sunIcon = themeToggle.querySelector('.fa-sun');
    
    // Check for saved theme preference and apply it
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
        moonIcon.style.display = 'none';
        sunIcon.style.display = 'block';
    }
    
    // Toggle theme on button click
    themeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-theme');
        
        // Toggle icons
        if (document.body.classList.contains('dark-theme')) {
            moonIcon.style.display = 'none';
            sunIcon.style.display = 'block';
            localStorage.setItem('theme', 'dark');
        } else {
            moonIcon.style.display = 'block';
            sunIcon.style.display = 'none';
            localStorage.setItem('theme', 'light');
        }
    });

    // Search functionality
    const searchInput = document.querySelector('.search-input');
    const searchIcon = document.querySelector('.search-icon');

    if (searchInput && searchIcon) {
        // Handle search icon click
        searchIcon.addEventListener('click', function() {
            performSearch();
        });

        // Handle Enter key press in search input
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }

    // Function to perform search
    function performSearch() {
        const searchTerm = searchInput.value.trim();
        if (searchTerm) {
            console.log('Searching for:', searchTerm);
            // Implement the actual search functionality
            // window.location.href = 'search-results.php?q=' + encodeURIComponent(searchTerm);
            alert('Search functionality will be implemented soon!');
        }
    }
    
    // Auto-dismiss notifications after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        alerts.forEach(alert => {
            setTimeout(() => {
                // Add fade-out animation
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                
                // Remove the element after animation completes
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 5000); // Wait 5 seconds before starting to fade out
        });
    }
    
    // Initialize all major UI components
    initSlider();
    initTestimonialsSlider();
    initTrendingSection();
    initNumberInputs();
    console.log('About to call initTravelExplorer');
    initTravelExplorer(); // Initialize the travel explorer component
    console.log('Called initTravelExplorer');
    
    // Check if items are in wishlist on page load
    checkWishlistStatus();
    
    // Enable form validation for any forms on the page
    const forms = document.querySelectorAll('form');
    if (forms.length > 0) {
        forms.forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // Find all invalid inputs and add visual cues
                    const invalidInputs = form.querySelectorAll(':invalid');
                    invalidInputs.forEach(input => {
                        input.classList.add('is-invalid');
                        
                        // Add an error message if not already present
                        const parent = input.parentElement;
                        if (!parent.querySelector('.error-message')) {
                            const errorMsg = document.createElement('div');
                            errorMsg.className = 'error-message';
                            errorMsg.style.color = '#dc3545';
                            errorMsg.style.fontSize = '0.875rem';
                            errorMsg.style.marginTop = '5px';
                            errorMsg.textContent = input.validationMessage;
                            parent.appendChild(errorMsg);
                        }
                    });
                }
                
                form.classList.add('was-validated');
            }, false);
            
            // Remove error styling when input changes
            form.querySelectorAll('input, select, textarea').forEach(input => {
                input.addEventListener('input', function() {
                    if (this.checkValidity()) {
                        this.classList.remove('is-invalid');
                        
                        // Find the closest form-group parent
                        const parent = this.closest('.form-group');
                        if (parent) {
                            const errorMsg = parent.querySelector('.error-message');
                            if (errorMsg) {
                                errorMsg.remove();
                            }
                        }
                    }
                });
            });
        });
    }
    
    // Add tooltips to disabled fields to provide additional information
    const disabledInputs = document.querySelectorAll('input:disabled, select:disabled, textarea:disabled');
    disabledInputs.forEach(input => {
        input.title = input.title || "This field cannot be edited";
        
        // Add hover effect to show it's disabled but interactive for tooltips
        input.addEventListener('mouseover', function() {
            this.style.cursor = 'not-allowed';
        });
    });
    
    // Handle readonly fields with better tooltips
    const readonlyInputs = document.querySelectorAll('input[readonly], textarea[readonly]');
    readonlyInputs.forEach(input => {
        // Add a descriptive title if not already specified
        if (!input.title) {
            if (input.id === 'email_display' || input.id === 'email') {
                input.title = "Email address cannot be changed here. Contact support for assistance.";
            } else {
                input.title = "This field is read-only";
            }
        }
        
        // Prevent user from selecting text in readonly fields for better UX
        input.addEventListener('focus', function(e) {
            // Allow text selection in textarea elements
            if (this.tagName.toLowerCase() !== 'textarea') {
                // Move cursor to end and prevent text selection
                const val = this.value;
                this.value = '';
                this.value = val;
            }
        });
    });
    
    // Handle mobile menu toggle if present
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuToggle && navLinks) {
        mobileMenuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            mobileMenuToggle.classList.toggle('active');
        });
    }
    
    // Apply form field animations
    const formControls = document.querySelectorAll('.form-control');
    formControls.forEach(input => {
        // Add focus style
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        // Remove focus style
        input.addEventListener('blur', function() {
            if (this.value.length === 0) {
                this.parentElement.classList.remove('focused');
            }
        });
        
        // Check on load if the field has a value
        if (input.value.length > 0) {
            input.parentElement.classList.add('focused');
        }
    });
    
    // Destination Image Slider
    const initSlider = () => {
        const sliderWrapper = document.querySelector('.slider-wrapper');
        const slides = document.querySelectorAll('.slide');
        const prevBtn = document.querySelector('.prev-btn');
        const nextBtn = document.querySelector('.next-btn');
        const indicatorsContainer = document.querySelector('.slide-indicators');
        const sliderContainer = document.querySelector('.destination-slider');
        
        if (!sliderWrapper || slides.length === 0) return;
        
        let currentIndex = 0;
        let autoplayInterval;
        const autoplayDelay = 6000; // 6 seconds between slides
        let isTransitioning = false; // Flag to prevent multiple rapid transitions
        
        // Create indicators dynamically
        slides.forEach((_, index) => {
            const indicator = document.createElement('div');
            indicator.classList.add('indicator');
            if (index === 0) indicator.classList.add('active');
            indicator.addEventListener('click', () => {
                if (!isTransitioning) goToSlide(index);
            });
            indicatorsContainer.appendChild(indicator);
        });
        
        // Update the slide wrapper transform to show the current slide
        const updateSlider = () => {
            isTransitioning = true;
            
            // Smooth transition with proper easing
            sliderWrapper.style.transform = `translateX(-${currentIndex * 100}%)`;
            
            // Update indicators
            document.querySelectorAll('.indicator').forEach((indicator, index) => {
                if (index === currentIndex) {
                    indicator.classList.add('active');
                } else {
                    indicator.classList.remove('active');
                }
            });
            
            // Update active class on slides
            slides.forEach((slide, index) => {
                if (index === currentIndex) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                }
            });
            
            // Reset isTransitioning after animation completes
            setTimeout(() => {
                isTransitioning = false;
            }, 800); // Match this with the CSS transition duration
        };
        
        // Go to a specific slide
        const goToSlide = (index) => {
            if (isTransitioning) return; // Prevent rapid transitions
            
            currentIndex = index;
            updateSlider();
            resetAutoplay();
        };
        
        // Previous slide with smooth wrap-around
        const prevSlide = () => {
            if (isTransitioning) return; // Prevent rapid transitions
            
            currentIndex = (currentIndex === 0) ? slides.length - 1 : currentIndex - 1;
            updateSlider();
            resetAutoplay();
        };
        
        // Next slide with smooth wrap-around
        const nextSlide = () => {
            if (isTransitioning) return; // Prevent rapid transitions
            
            currentIndex = (currentIndex === slides.length - 1) ? 0 : currentIndex + 1;
            updateSlider();
            resetAutoplay();
        };
        
        // Start autoplay
        const startAutoplay = () => {
            autoplayInterval = setInterval(nextSlide, autoplayDelay);
        };
        
        // Reset autoplay
        const resetAutoplay = () => {
            clearInterval(autoplayInterval);
            startAutoplay();
        };
        
        // Event listeners with improved handling
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            prevSlide();
            
            // Visual feedback for button click
            prevBtn.style.transform = 'translateY(-50%) scale(0.9)';
            setTimeout(() => {
                prevBtn.style.transform = 'translateY(-50%) scale(0.95)';
            }, 150);
        });
        
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            nextSlide();
            
            // Visual feedback for button click
            nextBtn.style.transform = 'translateY(-50%) scale(0.9)';
            setTimeout(() => {
                nextBtn.style.transform = 'translateY(-50%) scale(0.95)';
            }, 150);
        });
        
        // Improved touch swipe support with better threshold detection
        let touchStartX = 0;
        let touchEndX = 0;
        let touchStartY = 0;
        let touchEndY = 0;
        
        const handleSwipe = () => {
            if (isTransitioning) return;
            
            const swipeThreshold = 50; // Minimum distance for swipe
            const verticalThreshold = 50; // Maximum vertical movement to still consider it a horizontal swipe
            const diffX = touchEndX - touchStartX;
            const diffY = Math.abs(touchEndY - touchStartY);
            
            // Only treat as swipe if horizontal movement is greater than vertical movement
            if (diffY < verticalThreshold) {
                if (diffX > swipeThreshold) {
                    // Swiped right, go to previous
                    prevSlide();
                } else if (diffX < -swipeThreshold) {
                    // Swiped left, go to next
                    nextSlide();
                }
            }
        };
        
        sliderWrapper.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
            touchStartY = e.changedTouches[0].screenY;
        });
        
        sliderWrapper.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            touchEndY = e.changedTouches[0].screenY;
            handleSwipe();
        });
        
        // Add mouse swipe support for desktop
        let mouseDown = false;
        let mouseStartX = 0;
        let mouseStartY = 0;
        
        sliderWrapper.addEventListener('mousedown', (e) => {
            mouseDown = true;
            mouseStartX = e.clientX;
            mouseStartY = e.clientY;
            sliderWrapper.style.cursor = 'grabbing';
        });
        
        sliderWrapper.addEventListener('mouseup', (e) => {
            if (mouseDown) {
                mouseDown = false;
                sliderWrapper.style.cursor = 'grab';
                
                const diffX = e.clientX - mouseStartX;
                const diffY = Math.abs(e.clientY - mouseStartY);
                const minSwipeDistance = 80;
                
                if (diffY < 50) { // Ignore if vertical movement is too much
                    if (diffX > minSwipeDistance) {
                        prevSlide();
                    } else if (diffX < -minSwipeDistance) {
                        nextSlide();
                    }
                }
            }
        });
        
        sliderWrapper.addEventListener('mousemove', (e) => {
            if (mouseDown) {
                e.preventDefault(); // Prevent text selection during drag
            }
        });
        
        sliderWrapper.addEventListener('mouseleave', () => {
            mouseDown = false;
            sliderWrapper.style.cursor = 'grab';
        });
        
        // Pause autoplay on hover
        sliderContainer.addEventListener('mouseenter', () => {
            clearInterval(autoplayInterval);
        });
        
        sliderContainer.addEventListener('mouseleave', () => {
            startAutoplay();
            mouseDown = false;
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            // Only handle keys when slider is in viewport
            const rect = sliderContainer.getBoundingClientRect();
            const isInViewport = (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
            
            if (!isInViewport) return;
                
            if (e.key === 'ArrowLeft') {
                prevSlide();
            } else if (e.key === 'ArrowRight') {
                nextSlide();
            }
        });
        
        // Make slider focusable for keyboard navigation
        sliderContainer.setAttribute('tabindex', '0');
        sliderContainer.style.cursor = 'grab';
        
        // Add transition end listener
        sliderWrapper.addEventListener('transitionend', () => {
            isTransitioning = false;
        });
        
        // Initialize
        updateSlider();
        startAutoplay();
    };
    
    // Initialize the slider
    initSlider();

    // Function for Why Book With Us slider
    function initTestimonialsSlider() {
        console.log('Initializing testimonials slider');
        
        const testimonialsWrapper = document.querySelector('.testimonials-wrapper');
        const slides = document.querySelectorAll('.testimonial-slide');
        const prevBtn = document.querySelector('.testimonial-prev');
        const nextBtn = document.querySelector('.testimonial-next');
        const indicatorsContainer = document.querySelector('.testimonial-indicators');
        
        if (!testimonialsWrapper || slides.length === 0) {
            console.log('Testimonials slider elements not found');
            return;
        }
        
        // Create indicators
        indicatorsContainer.innerHTML = '';
        slides.forEach((_, index) => {
            const indicator = document.createElement('span');
            indicator.classList.add('indicator');
            if (index === 0) indicator.classList.add('active');
            indicator.dataset.index = index;
            indicatorsContainer.appendChild(indicator);
            
            // Add click event to each indicator
            indicator.addEventListener('click', () => {
                goToTestimonialSlide(index);
            });
        });
        
        let currentTestimonial = 0;
        
        function updateTestimonialSlider() {
            testimonialsWrapper.style.transform = `translateX(-${currentTestimonial * 100}%)`;
            
            // Update indicators
            const indicators = document.querySelectorAll('.testimonial-indicators .indicator');
            indicators.forEach((indicator, index) => {
                indicator.classList.toggle('active', index === currentTestimonial);
            });
        }
        
        // Initial setup
        updateTestimonialSlider();
        
        function goToTestimonialSlide(index) {
            currentTestimonial = index;
            updateTestimonialSlider();
        }
        
        function nextTestimonial() {
            currentTestimonial = (currentTestimonial + 1) % slides.length;
            updateTestimonialSlider();
        }
        
        function prevTestimonial() {
            currentTestimonial = (currentTestimonial - 1 + slides.length) % slides.length;
            updateTestimonialSlider();
        }
        
        // Add event listeners to buttons
        if (prevBtn) prevBtn.addEventListener('click', prevTestimonial);
        if (nextBtn) nextBtn.addEventListener('click', nextTestimonial);
        
        // Setup touch swipe support
        let touchStartX = 0;
        let touchEndX = 0;
        
        testimonialsWrapper.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        testimonialsWrapper.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });
        
        function handleSwipe() {
            const swipeThreshold = 50;
            if (touchStartX - touchEndX > swipeThreshold) {
                // Swipe left
                nextTestimonial();
            } else if (touchEndX - touchStartX > swipeThreshold) {
                // Swipe right
                prevTestimonial();
            }
        }
        
        // Auto-slide
        let interval = setInterval(nextTestimonial, 5000);
        
        // Pause auto-slide on hover
        testimonialsWrapper.addEventListener('mouseenter', () => {
            clearInterval(interval);
        });
        
        testimonialsWrapper.addEventListener('mouseleave', () => {
            interval = setInterval(nextTestimonial, 5000);
        });
    }
    
    // Initialize the testimonials slider
    initTestimonialsSlider();
    
    // Initialize Number Input Components
    initNumberInputs();

    // Add event listeners for trending tour cards
    // Handle view details buttons for tour cards
    const viewDetailsButtons = document.querySelectorAll('.view-details-btn');
    viewDetailsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tourName = this.closest('.tour-card').querySelector('h3').textContent;
            // For now just show an alert, in a real app this would navigate to a details page
            alert(`You will be redirected to ${tourName} details page.`);
            // Future implementation: window.location.href = `/tour-details.php?tour=${encodeURIComponent(tourName)}`;
        });
    });

    // Handle "View All Destinations" button
    const viewAllBtn = document.querySelector('.view-all-btn');
    if (viewAllBtn) {
        viewAllBtn.addEventListener('click', function() {
            // For now just show an alert, in a real app this would navigate to a listing page
            alert('You will be redirected to all destinations page.');
            // Future implementation: window.location.href = '/destinations.php';
        });
    }

    // Add hover effects for tour cards
    const tourCards = document.querySelectorAll('.tour-card');
    tourCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.classList.add('hovered');
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('hovered');
        });
    });

    // Add dynamic elements and enhanced interaction to tour cards
    initTourCards();

    // Add an automatic image slider function to the tour cards
    const tourImageInterval = setInterval(() => {
        const visibleCards = Array.from(document.querySelectorAll('.tour-card'))
            .filter(card => {
                const rect = card.getBoundingClientRect();
                return (
                    rect.top >= 0 &&
                    rect.left >= 0 &&
                    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
                );
            });
            
        if (visibleCards.length > 0) {
            // Select a random visible card
            const randomCard = visibleCards[Math.floor(Math.random() * visibleCards.length)];
            const img = randomCard.querySelector('.tour-image img');
            
            // Add a subtle pulse animation
            if (img) {
                img.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    img.style.transform = '';
                }, 500);
            }
        }
    }, 3000);
    
    // Clear the interval when the user leaves the page
    window.addEventListener('beforeunload', () => {
        clearInterval(tourImageInterval);
    });

    // Handle favorite buttons for tour cards
    const favoriteButtons = document.querySelectorAll('.card-favorite');
    
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Toggle active class
            this.classList.toggle('active');
            
            // Change icon
            const icon = this.querySelector('i');
            if (this.classList.contains('active')) {
                icon.className = 'fas fa-heart';
                // Optional: Show feedback to user
                showNotification('Added to favorites!');
            } else {
                icon.className = 'far fa-heart';
                // Optional: Show feedback to user
                showNotification('Removed from favorites.');
            }
        });
    });
    
    // Simple notification function
    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'favorite-notification';
        notification.textContent = message;
        
        // Add styles for the notification
        notification.style.position = 'fixed';
        notification.style.bottom = '20px';
        notification.style.right = '20px';
        notification.style.backgroundColor = '#FF5722';
        notification.style.color = 'white';
        notification.style.padding = '10px 20px';
        notification.style.borderRadius = '5px';
        notification.style.boxShadow = '0 3px 10px rgba(0,0,0,0.2)';
        notification.style.zIndex = '9999';
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(20px)';
        notification.style.transition = 'all 0.3s ease';
        
        document.body.appendChild(notification);
        
        // Show notification with animation
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateY(0)';
        }, 10);
        
        // Remove notification after 2 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(20px)';
            
            // Remove from DOM after animation
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 2000);
    }

    // Add this function to initialize the accordion tours
    function initAccordionTours() {
        const accordionTours = document.querySelectorAll('.accordion-tour');
        
        if (accordionTours.length === 0) return;
        
        // Set first item as expanded by default if none are expanded
        let hasExpanded = false;
        accordionTours.forEach(tour => {
            if (tour.getAttribute('data-expanded') === 'true') {
                hasExpanded = true;
            }
        });
        
        if (!hasExpanded && accordionTours.length > 0) {
            accordionTours[0].setAttribute('data-expanded', 'true');
        }
        
        accordionTours.forEach(tour => {
            // Handle click on the accordion toggle button
            const toggleBtn = tour.querySelector('.accordion-toggle');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const isExpanded = tour.getAttribute('data-expanded') === 'true';
                    
                    // Toggle the current accordion
                    if (isExpanded) {
                        tour.setAttribute('data-expanded', 'false');
                    } else {
                        tour.setAttribute('data-expanded', 'true');
                    }
                });
            }
            
            // Handle click on the entire tour card
            tour.addEventListener('click', function(e) {
                // Don't trigger if clicking on the toggle button or favorite button
                if (!e.target.closest('.accordion-toggle') && !e.target.closest('.card-favorite') && !e.target.closest('.view-details-btn')) {
                    const isExpanded = this.getAttribute('data-expanded') === 'true';
                    
                    // Toggle the current accordion
                    if (isExpanded) {
                        this.setAttribute('data-expanded', 'false');
                    } else {
                        this.setAttribute('data-expanded', 'true');
                    }
                }
            });
            
            // Handle view details button
            const viewDetailsBtn = tour.querySelector('.view-details-btn');
            if (viewDetailsBtn) {
                viewDetailsBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const tourName = tour.querySelector('h3').textContent;
                    // For now just show an alert, in a real app this would navigate to a details page
                    alert(`You will be redirected to ${tourName} details page.`);
                    // Future implementation: window.location.href = `/tour-details.php?tour=${encodeURIComponent(tourName)}`;
                });
            }
        });
    }

    // Initialize the accordion tours
    initAccordionTours();

    // Trending Tours Section Functionality
    initTrendingSection();
});

function initTrendingSection() {
    // Implementation of initTrendingSection function
    console.log('Trending section initialization');
}

function initNumberInputs() {
    // Implementation of initNumberInputs function
    console.log('Number inputs initialization');
}

function initTourCards() {
    // Implementation of initTourCards function
    console.log('Tour cards initialization');
}

function checkWishlistStatus() {
    // Implementation of checkWishlistStatus function
    console.log('Wishlist status check');
}

function ensureWishlistTable() {
    // Implementation of ensureWishlistTable function
    console.log('Wishlist table check');
}

function initDashboard() {
    initThemeToggle();
    initBookingCards();
    initNotifications();
    initTestimonialsSlider();
    initTrendingTours();
    initProfileDropdown();
    initSidebar();
    initStatistics();
    initToasts();
    initPopovers();
    initToolTips();
    initDataTable();
    initDatePickers();
    initCharts();
}

// Function to initialize the Travel Explorer component
function initTravelExplorer() {
    console.log('Initializing Travel Explorer component');
    
    // Set up view toggle functionality
    const viewToggleBtns = document.querySelectorAll('.view-toggle-btn');
    const cardsContainer = document.querySelector('.cards-container');
    
    if (viewToggleBtns.length && cardsContainer) {
        viewToggleBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                viewToggleBtns.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Set view mode
                const viewMode = this.getAttribute('data-view');
                cardsContainer.className = 'cards-container ' + viewMode + '-view';
            });
        });
    }
    
    // Set up destination filtering
    const filterDropdown = document.getElementById('filter-dropdown');
    const searchInput = document.getElementById('explorer-search-input');
    const travelCards = document.querySelectorAll('.travel-card');
    
    if (filterDropdown) {
        filterDropdown.addEventListener('change', filterDestinations);
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', filterDestinations);
    }
    
    function filterDestinations() {
        const searchTerm = (searchInput ? searchInput.value.toLowerCase() : '');
        const filterValue = (filterDropdown ? filterDropdown.value : 'all');
        
        travelCards.forEach(card => {
            const category = card.getAttribute('data-category') || '';
            const title = card.querySelector('.card-title').textContent.toLowerCase();
            const description = card.querySelector('.card-description').textContent.toLowerCase();
            
            // Check if card matches search term
            const matchesSearch = searchTerm === '' || 
                title.includes(searchTerm) || 
                description.includes(searchTerm);
            
            // Check if card matches filter
            const matchesFilter = filterValue === 'all' || category.includes(filterValue);
            
            // Show/hide card based on filters
            if (matchesSearch && matchesFilter) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Initialize image galleries in travel cards
    initializeImageGalleries();
    
    // Fix missing images with fallbacks
    fixMissingImages();
}

// Function to initialize image galleries in travel cards
function initializeImageGalleries() {
    const travelCards = document.querySelectorAll('.travel-card');
    
    travelCards.forEach(card => {
        const imageContainer = card.querySelector('.image-container');
        const dots = card.querySelectorAll('.image-dot');
        const prevBtn = card.querySelector('.image-nav.prev');
        const nextBtn = card.querySelector('.image-nav.next');
        
        if (!imageContainer || dots.length === 0) return;
        
        let currentIndex = 0;
        const maxIndex = dots.length - 1;
        
        // Set up image navigation
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                currentIndex = (currentIndex > 0) ? currentIndex - 1 : maxIndex;
                updateGallery();
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                currentIndex = (currentIndex < maxIndex) ? currentIndex + 1 : 0;
                updateGallery();
            });
        }
        
        // Set up dot navigation
        dots.forEach((dot, index) => {
            dot.addEventListener('click', function(e) {
                e.stopPropagation();
                currentIndex = index;
                updateGallery();
            });
        });
        
        // Function to update gallery display
        function updateGallery() {
            // Update transform
            imageContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
            
            // Update active dot
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentIndex);
            });
        }
    });
}

// Function to fix missing images with fallbacks
function fixMissingImages() {
    const cardImages = document.querySelectorAll('.travel-card .image-container img');
    
    cardImages.forEach(img => {
        // Handle missing images
        img.onerror = function() {
            console.log('Image failed to load:', this.src);
            this.classList.add('fallback-img');
            
            // Use a data URI as fallback
            this.src = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22600%22%20height%3D%22400%22%20viewBox%3D%220%200%20600%20400%22%3E%3Crect%20width%3D%22600%22%20height%3D%22400%22%20fill%3D%22%23f0f2f5%22%2F%3E%3Ctext%20x%3D%22300%22%20y%3D%22200%22%20font-family%3D%22Arial%2C%20sans-serif%22%20font-size%3D%2218%22%20fill%3D%22%23999%22%20text-anchor%3D%22middle%22%20dominant-baseline%3D%22middle%22%3EDestination%20Image%3C%2Ftext%3E%3C%2Fsvg%3E';
        };
        
        // Handle already-broken images
        if (img.complete && (img.naturalWidth === 0 || img.naturalHeight === 0)) {
            img.onerror();
        }
    });
}

// Enhanced Dashboard Functionality
function improveTestimonialSlider() {
    const testimonialsWrapper = document.querySelector('.testimonials-wrapper');
    const slides = document.querySelectorAll('.testimonial-slide');
    const prevBtn = document.querySelector('.testimonial-prev');
    const nextBtn = document.querySelector('.testimonial-next');
    const indicatorsContainer = document.querySelector('.testimonial-indicators');
    
    if (!testimonialsWrapper || slides.length === 0) return;
    
    // Create indicators if they don't exist
    if (indicatorsContainer) {
        indicatorsContainer.innerHTML = '';
        slides.forEach((_, index) => {
            const indicator = document.createElement('span');
            indicator.classList.add('indicator');
            if (index === 0) indicator.classList.add('active');
            indicator.dataset.index = index;
            indicatorsContainer.appendChild(indicator);
            
            // Add click event to each indicator
            indicator.addEventListener('click', () => {
                goToSlide(index);
            });
        });
    }
    
    let currentSlide = 0;
    const slideWidth = slides[0].clientWidth;
    
    // Setup initial position
    updateSliderPosition();
    
    // Event listeners for buttons
    if (prevBtn) prevBtn.addEventListener('click', prevSlide);
    if (nextBtn) nextBtn.addEventListener('click', nextSlide);
    
    // Add touch swipe support
    let touchStartX = 0;
    let touchEndX = 0;
    
    testimonialsWrapper.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    testimonialsWrapper.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
    
    function handleSwipe() {
        if (touchStartX - touchEndX > 50) {
            // Swipe left
            nextSlide();
        } else if (touchEndX - touchStartX > 50) {
            // Swipe right
            prevSlide();
        }
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        updateSliderPosition();
    }
    
    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        updateSliderPosition();
    }
    
    function goToSlide(index) {
        currentSlide = index;
        updateSliderPosition();
    }
    
    function updateSliderPosition() {
        testimonialsWrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
        
        // Update indicators
        const indicators = document.querySelectorAll('.testimonial-indicators .indicator');
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === currentSlide);
        });
    }
    
    // Auto-slide
    let interval = setInterval(nextSlide, 5000);
    
    // Pause auto-slide on hover
    testimonialsWrapper.addEventListener('mouseenter', () => {
        clearInterval(interval);
    });
    
    testimonialsWrapper.addEventListener('mouseleave', () => {
        interval = setInterval(nextSlide, 5000);
    });
}

function improveImageGallery() {
    const travelCards = document.querySelectorAll('.travel-card');
    
    travelCards.forEach(card => {
        const imageContainer = card.querySelector('.image-container');
        const images = card.querySelectorAll('.image-container img');
        const dots = card.querySelectorAll('.image-dot');
        const prevBtn = card.querySelector('.image-nav.prev');
        const nextBtn = card.querySelector('.image-nav.next');
        
        if (!imageContainer || images.length <= 1) return;
        
        let currentImage = 0;
        
        // Setup initial state
        updateGallery(currentImage);
        
        // Event listeners
        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                currentImage = (currentImage - 1 + images.length) % images.length;
                updateGallery(currentImage);
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                currentImage = (currentImage + 1) % images.length;
                updateGallery(currentImage);
            });
        }
        
        // Dot navigation
        dots.forEach((dot, index) => {
            dot.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                currentImage = index;
                updateGallery(currentImage);
            });
        });
        
        function updateGallery(index) {
            imageContainer.style.transform = `translateX(-${index * 100}%)`;
            
            // Update dots
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });
        }
    });
}

function enhanceFormFields() {
    // Add date validation for check-in/check-out
    const checkInInput = document.getElementById('hotel-check-in');
    const checkOutInput = document.getElementById('hotel-check-out');
    
    if (checkInInput && checkOutInput) {
        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        checkInInput.min = today;
        
        // Ensure check-out date is after check-in date
        checkInInput.addEventListener('change', () => {
            checkOutInput.min = checkInInput.value;
            
            // If check-out date is before new check-in date, update it
            if (checkOutInput.value && checkOutInput.value < checkInInput.value) {
                checkOutInput.value = checkInInput.value;
            }
        });
    }
    
    // Similar validation for flight dates
    const flightDepartInput = document.getElementById('flight-depart');
    const flightReturnInput = document.getElementById('flight-return');
    
    if (flightDepartInput && flightReturnInput) {
        const today = new Date().toISOString().split('T')[0];
        flightDepartInput.min = today;
        
        flightDepartInput.addEventListener('change', () => {
            flightReturnInput.min = flightDepartInput.value;
            
            if (flightReturnInput.value && flightReturnInput.value < flightDepartInput.value) {
                flightReturnInput.value = flightDepartInput.value;
            }
        });
    }
    
    // Add destination autocomplete placeholder functionality
    const destinationInputs = document.querySelectorAll('input[id$="-destination"], input[id$="-from"], input[id$="-to"]');
    
    destinationInputs.forEach(input => {
        input.addEventListener('focus', () => {
            // This would be replaced with actual autocomplete functionality
            input.placeholder = 'Type to search...';
        });
        
        input.addEventListener('blur', () => {
            input.placeholder = input.getAttribute('data-placeholder') || 'Where are you going?';
        });
        
        // Store original placeholder
        input.setAttribute('data-placeholder', input.placeholder);
    });
}

function improveBookingCards() {
    const bookingCards = document.querySelectorAll('.booking-card');
    
    bookingCards.forEach(card => {
        // Add animation on hover
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-8px)';
            card.style.boxShadow = '0 15px 30px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.08)';
        });
        
        // Improve search button
        const searchBtn = card.querySelector('.booking-search-btn');
        if (searchBtn) {
            // Add ripple effect
            searchBtn.addEventListener('click', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const ripple = document.createElement('span');
                ripple.classList.add('ripple');
                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600); // Match the CSS animation duration
            });
            
            // Add loading state
            searchBtn.addEventListener('click', function() {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Searching...';
                this.disabled = true;
                
                // Simulate search delay
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                    
                    // Show success toast
                    showToast('Search completed! Results will be available soon.');
                }, 2000);
            });
        }
    });
}

function enhanceSearchExperience() {
    const searchInput = document.querySelector('.search-input');
    if (!searchInput) return;
    
    // Create container for recent searches
    const searchContainer = searchInput.closest('.search-container');
    
    if (searchContainer) {
        let recentSearches = document.createElement('div');
        recentSearches.classList.add('recent-searches');
        recentSearches.style.position = 'absolute';
        recentSearches.style.top = '100%';
        recentSearches.style.left = '0';
        recentSearches.style.width = '100%';
        recentSearches.style.background = 'white';
        recentSearches.style.borderRadius = '0 0 12px 12px';
        recentSearches.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.1)';
        recentSearches.style.zIndex = '1000';
        recentSearches.style.maxHeight = '0';
        recentSearches.style.overflow = 'hidden';
        recentSearches.style.transition = 'max-height 0.3s ease, padding 0.3s ease';
        searchContainer.appendChild(recentSearches);
        
        // Set up recent searches interaction
        searchInput.addEventListener('focus', () => {
            const hasRecentSearches = loadRecentSearches();
            
            if (hasRecentSearches) {
                recentSearches.style.maxHeight = '300px';
                recentSearches.style.padding = '10px 0';
            }
        });
        
        // Hide recent searches when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchContainer.contains(e.target)) {
                recentSearches.style.maxHeight = '0';
                recentSearches.style.padding = '0';
            }
        });
        
        // Save search when submitting
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && searchInput.value.trim()) {
                saveRecentSearch(searchInput.value.trim());
            }
        });
    }
    
    // Load recent searches from localStorage and display them
    function loadRecentSearches() {
        const recentSearchesData = JSON.parse(localStorage.getItem('recentSearches') || '[]');
        
        if (recentSearchesData.length === 0) {
            return false;
        }
        
        displayRecentSearches(recentSearchesData);
        return true;
    }
    
    // Save a search term to localStorage
    function saveRecentSearch(term) {
        const recentSearches = JSON.parse(localStorage.getItem('recentSearches') || '[]');
        
        // Remove duplicate if it exists
        const index = recentSearches.indexOf(term);
        if (index > -1) {
            recentSearches.splice(index, 1);
        }
        
        // Add new search term at the beginning
        recentSearches.unshift(term);
        
        // Keep only the most recent 5 searches
        if (recentSearches.length > 5) {
            recentSearches.pop();
        }
        
        localStorage.setItem('recentSearches', JSON.stringify(recentSearches));
    }
    
    function displayRecentSearches(searches) {
        const recentSearchesElement = document.querySelector('.recent-searches');
        recentSearchesElement.innerHTML = '<h4 style="margin: 0 0 10px; padding: 0 15px; font-size: 0.9rem; color: #666;">Recent Searches</h4>';
        
        searches.forEach(search => {
            const searchItem = document.createElement('div');
            searchItem.classList.add('recent-search-item');
            searchItem.style.padding = '8px 15px';
            searchItem.style.cursor = 'pointer';
            searchItem.style.transition = 'background 0.2s ease';
            searchItem.innerHTML = `<i class="fas fa-history" style="margin-right: 10px; color: #999;"></i> ${search}`;
            
            searchItem.addEventListener('mouseover', () => {
                searchItem.style.background = 'rgba(0, 0, 0, 0.05)';
            });
            
            searchItem.addEventListener('mouseout', () => {
                searchItem.style.background = 'transparent';
            });
            
            searchItem.addEventListener('click', () => {
                searchInput.value = search;
                recentSearchesElement.style.maxHeight = '0';
                recentSearchesElement.style.padding = '0';
                
                // Trigger search
                const searchButton = document.querySelector('.search-icon');
                if (searchButton) {
                    searchButton.click();
                }
            });
            
            recentSearchesElement.appendChild(searchItem);
        });
    }
}

function enhanceFooter() {
    // Add animation to social media icons
    const socialIcons = document.querySelectorAll('.social-icon');
    
    socialIcons.forEach((icon, index) => {
        // Add staggered delay to entrance animation
        icon.style.transitionDelay = `${index * 0.1}s`;
        
        // Add hover effect
        icon.addEventListener('mouseenter', () => {
            icon.style.transform = 'translateY(-5px)';
            icon.style.boxShadow = '0 10px 15px rgba(0, 0, 0, 0.2)';
        });
        
        icon.addEventListener('mouseleave', () => {
            icon.style.transform = '';
            icon.style.boxShadow = '';
        });
    });
    
    // Add smooth scroll for footer links
    const footerLinks = document.querySelectorAll('.site-footer a[href^="#"]');
    
    footerLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Call enhancement functions when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Testimonial slider improvements
    improveTestimonialSlider();
    
    // Travel card image gallery improvements
    improveImageGallery();
    
    // Add responsive functionality to forms
    enhanceFormFields();
    
    // Improve booking card transitions
    improveBookingCards();
    
    // Add search functionality
    enhanceSearchExperience();
    
    // Enhance footer
    enhanceFooter();
});

// Destination slider functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize destination slider
    const destinationsSlider = document.querySelector('.destinations-slider');
    const destinationPrev = document.querySelector('.destination-prev');
    const destinationNext = document.querySelector('.destination-next');
    
    if (destinationsSlider && destinationPrev && destinationNext) {
        let isDown = false;
        let startX;
        let scrollLeft;
        let slideWidth = 375; // Width of destination card + gap
        let slideIndex = 0;
        let autoplayInterval;
        
        // Calculate total slides and visible slides
        const calculateSlides = () => {
            const cards = destinationsSlider.querySelectorAll('.destination-card');
            const containerWidth = destinationsSlider.parentElement.offsetWidth;
            const visibleSlides = Math.floor(containerWidth / slideWidth);
            const totalSlides = cards.length;
            return { visibleSlides, totalSlides };
        };
        
        // Update slide width on resize
        const updateSlideWidth = () => {
            const windowWidth = window.innerWidth;
            if (windowWidth < 576) {
                slideWidth = 265; // Card width + gap for mobile
            } else if (windowWidth < 768) {
                slideWidth = 300; // Card width + gap for tablet
            } else if (windowWidth < 992) {
                slideWidth = 320; // Card width + gap for small desktop
            } else {
                slideWidth = 375; // Card width + gap for large desktop
            }
        };
        
        // Move to specific slide
        const goToSlide = (index) => {
            const { totalSlides, visibleSlides } = calculateSlides();
            const maxIndex = Math.max(0, totalSlides - visibleSlides);
            slideIndex = Math.min(Math.max(0, index), maxIndex);
            destinationsSlider.style.transform = `translateX(-${slideIndex * slideWidth}px)`;
            
            // Update button states
            destinationPrev.style.opacity = slideIndex <= 0 ? "0.5" : "1";
            destinationPrev.style.pointerEvents = slideIndex <= 0 ? "none" : "auto";
            destinationNext.style.opacity = slideIndex >= maxIndex ? "0.5" : "1";
            destinationNext.style.pointerEvents = slideIndex >= maxIndex ? "none" : "auto";
            
            // Reset autoplay
            clearInterval(autoplayInterval);
            startAutoplay();
        };
        
        // Autoplay function
        const startAutoplay = () => {
            const { totalSlides, visibleSlides } = calculateSlides();
            const maxIndex = Math.max(0, totalSlides - visibleSlides);
            
            autoplayInterval = setInterval(() => {
                if (slideIndex >= maxIndex) {
                    goToSlide(0); // Loop back to the beginning
                } else {
                    goToSlide(slideIndex + 1);
                }
            }, 5000); // Change slide every 5 seconds
        };
        
        // Previous slide button
        destinationPrev.addEventListener('click', () => {
            goToSlide(slideIndex - 1);
        });
        
        // Next slide button
        destinationNext.addEventListener('click', () => {
            goToSlide(slideIndex + 1);
        });
        
        // Mouse drag functionality
        destinationsSlider.addEventListener('mousedown', (e) => {
            isDown = true;
            destinationsSlider.style.transition = 'none';
            startX = e.pageX - destinationsSlider.offsetLeft;
            scrollLeft = destinationsSlider.scrollLeft;
            
            // Clear autoplay when user interacts
            clearInterval(autoplayInterval);
        });
        
        destinationsSlider.addEventListener('mouseleave', () => {
            isDown = false;
            destinationsSlider.style.transition = 'transform 0.5s ease';
            
            // Restart autoplay
            clearInterval(autoplayInterval);
            startAutoplay();
        });
        
        destinationsSlider.addEventListener('mouseup', () => {
            isDown = false;
            destinationsSlider.style.transition = 'transform 0.5s ease';
            
            // Snap to closest slide
            const dragDistance = (scrollLeft - destinationsSlider.scrollLeft) * -1;
            if (Math.abs(dragDistance) > 100) {
                const direction = dragDistance > 0 ? -1 : 1;
                goToSlide(slideIndex + direction);
            } else {
                goToSlide(slideIndex);
            }
            
            // Restart autoplay
            clearInterval(autoplayInterval);
            startAutoplay();
        });
        
        destinationsSlider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - destinationsSlider.offsetLeft;
            const walk = (x - startX) * 2;
            const transform = -slideIndex * slideWidth + walk;
            destinationsSlider.style.transform = `translateX(${transform}px)`;
        });
        
        // Touch functionality for mobile
        destinationsSlider.addEventListener('touchstart', (e) => {
            isDown = true;
            destinationsSlider.style.transition = 'none';
            startX = e.touches[0].pageX - destinationsSlider.offsetLeft;
            scrollLeft = destinationsSlider.scrollLeft;
            
            // Clear autoplay when user interacts
            clearInterval(autoplayInterval);
        });
        
        destinationsSlider.addEventListener('touchend', () => {
            isDown = false;
            destinationsSlider.style.transition = 'transform 0.5s ease';
            
            // Snap to closest slide
            const dragDistance = (scrollLeft - destinationsSlider.scrollLeft) * -1;
            if (Math.abs(dragDistance) > 50) {
                const direction = dragDistance > 0 ? -1 : 1;
                goToSlide(slideIndex + direction);
            } else {
                goToSlide(slideIndex);
            }
            
            // Restart autoplay
            clearInterval(autoplayInterval);
            startAutoplay();
        });
        
        destinationsSlider.addEventListener('touchmove', (e) => {
            if (!isDown) return;
            const x = e.touches[0].pageX - destinationsSlider.offsetLeft;
            const walk = (x - startX) * 2;
            const transform = -slideIndex * slideWidth + walk;
            destinationsSlider.style.transform = `translateX(${transform}px)`;
        });
        
        // Initialize slider and respond to window resize
        updateSlideWidth();
        goToSlide(0);
        startAutoplay();
        
        window.addEventListener('resize', () => {
            updateSlideWidth();
            goToSlide(slideIndex);
        });
        
        // Add animations for destination cards
        const animateCards = () => {
            const destinationCards = document.querySelectorAll('.destination-card');
            destinationCards.forEach((card, index) => {
                // Staggered animation
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 * index);
                
                // Add hover effects
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px)';
                    
                    // Also animate the image
                    const img = this.querySelector('.destination-image img');
                    if (img) {
                        img.style.transform = 'scale(1.1)';
                    }
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    
                    // Reset image animation
                    const img = this.querySelector('.destination-image img');
                    if (img) {
                        img.style.transform = 'scale(1)';
                    }
                });
            });
        };
        
        // Call animation function
        animateCards();
        
        // Add click handler for view all button
        const viewAllBtn = document.querySelector('.view-all-btn');
        if (viewAllBtn) {
            viewAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                alert('View all destinations feature will be implemented soon!');
            });
        }
    }
}); 
