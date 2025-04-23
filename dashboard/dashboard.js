// Theme toggle functionality
document.addEventListener('DOMContentLoaded', function() {
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
        const testimonialsWrapper = document.querySelector('.testimonials-wrapper');
        const testimonialSlides = document.querySelectorAll('.testimonial-slide');
        const testimonialPrevBtn = document.querySelector('.testimonial-prev');
        const testimonialNextBtn = document.querySelector('.testimonial-next');
        const testimonialIndicatorsContainer = document.querySelector('.testimonial-indicators');
        
        if (!testimonialsWrapper || testimonialSlides.length === 0) {
            console.log('Testimonials slider elements not found');
            return;
        }
        
        console.log('Initializing testimonials slider with', testimonialSlides.length, 'slides');
        
        let currentTestimonialIndex = 0;
        
        // Create indicators
        testimonialIndicatorsContainer.innerHTML = ''; // Clear existing indicators if any
        testimonialSlides.forEach((_, index) => {
            const indicator = document.createElement('div');
            indicator.classList.add('indicator');
            if (index === 0) indicator.classList.add('active');
            indicator.addEventListener('click', () => goToTestimonialSlide(index));
            testimonialIndicatorsContainer.appendChild(indicator);
        });
        
        const testimonialIndicators = document.querySelectorAll('.testimonial-indicators .indicator');
        
        // Set initial state
        updateTestimonialSlider();
        
        function updateTestimonialSlider() {
            // Apply transform to slide wrapper
            testimonialsWrapper.style.transform = `translateX(${-currentTestimonialIndex * 100}%)`;
            
            // Update active classes on slides
            testimonialSlides.forEach((slide, index) => {
                if (index === currentTestimonialIndex) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                }
            });
            
            // Update active indicator
            testimonialIndicators.forEach((indicator, index) => {
                if (index === currentTestimonialIndex) {
                    indicator.classList.add('active');
                } else {
                    indicator.classList.remove('active');
                }
            });
        }
        
        function goToTestimonialSlide(index) {
            currentTestimonialIndex = index;
            updateTestimonialSlider();
        }
        
        function nextTestimonial() {
            currentTestimonialIndex = (currentTestimonialIndex + 1) % testimonialSlides.length;
            updateTestimonialSlider();
        }
        
        function prevTestimonial() {
            currentTestimonialIndex = (currentTestimonialIndex - 1 + testimonialSlides.length) % testimonialSlides.length;
            updateTestimonialSlider();
        }
        
        // Event listeners
        if (testimonialNextBtn) {
            testimonialNextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                nextTestimonial();
                console.log('Next testimonial clicked');
            });
        }
        
        if (testimonialPrevBtn) {
            testimonialPrevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                prevTestimonial();
                console.log('Previous testimonial clicked');
            });
        }
        
        // Auto slide testimonials every 5 seconds
        let testimonialInterval = setInterval(nextTestimonial, 5000);
        
        // Pause auto slide on hover
        const testimonialsSlider = document.querySelector('.testimonials-slider');
        if (testimonialsSlider) {
            testimonialsSlider.addEventListener('mouseenter', () => {
                clearInterval(testimonialInterval);
            });
            
            testimonialsSlider.addEventListener('mouseleave', () => {
                testimonialInterval = setInterval(nextTestimonial, 5000);
            });
        }
        
        // Touch events for mobile swipe
        let testimonialTouchStartX = 0;
        
        if (testimonialsSlider) {
            testimonialsSlider.addEventListener('touchstart', (e) => {
                testimonialTouchStartX = e.touches[0].clientX;
                clearInterval(testimonialInterval);
            }, { passive: true });
            
            testimonialsSlider.addEventListener('touchend', (e) => {
                const touchEndX = e.changedTouches[0].clientX;
                const diffX = testimonialTouchStartX - touchEndX;
                
                if (Math.abs(diffX) > 50) { // Minimum swipe distance
                    if (diffX > 0) {
                        nextTestimonial(); // Swipe left, go to next
                    } else {
                        prevTestimonial(); // Swipe right, go to previous
                    }
                }
                
                testimonialInterval = setInterval(nextTestimonial, 5000);
            }, { passive: true });
        }
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
                icon.classList.remove('far');
                icon.classList.add('fas');
                
                // Optional: Show a small notification
                showNotification('Added to favorites!');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                
                // Optional: Show a small notification
                showNotification('Removed from favorites!');
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
    // Initialize filter buttons
    const filterButtons = document.querySelectorAll('.filter-btn');
    const trendingCards = document.querySelectorAll('.trending-card');
    
    // Set initial active filter
    if (filterButtons.length > 0) {
        filterButtons[0].classList.add('active');
    }
    
    // Add click event to filter buttons
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filterValue = this.getAttribute('data-filter');
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter cards
            if (filterValue === 'all') {
                // Show all cards
                trendingCards.forEach(card => {
                    animateCardIn(card);
                });
            } else {
                // Filter cards by category
                trendingCards.forEach(card => {
                    const cardCategory = card.getAttribute('data-category');
                    
                    if (cardCategory === filterValue) {
                        animateCardIn(card);
                    } else {
                        animateCardOut(card);
                    }
                });
            }
        });
    });
    
    // Initialize favorite buttons
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('active');
            
            const icon = this.querySelector('i');
            if (icon) {
                if (icon.classList.contains('fa-heart-o')) {
                    icon.classList.remove('fa-heart-o');
                    icon.classList.add('fa-heart');
                    // Add heart animation
                    animateHeart(this);
                } else {
                    icon.classList.remove('fa-heart');
                    icon.classList.add('fa-heart-o');
                }
            }
            
            // You can add functionality to save favorites to local storage or database
            const tourId = this.closest('.trending-card').getAttribute('data-id');
            console.log(`Tour ${tourId} ${this.classList.contains('active') ? 'added to' : 'removed from'} favorites`);
        });
    });
    
    // Initialize share buttons
    const shareButtons = document.querySelectorAll('.share-btn');
    shareButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const tourCard = this.closest('.trending-card');
            const tourTitle = tourCard.querySelector('h3').textContent;
            const shareUrl = window.location.href;
            
            // Create a share message
            const shareText = `Check out this amazing destination: ${tourTitle}`;
            
            // Check if Web Share API is available
            if (navigator.share) {
                navigator.share({
                    title: tourTitle,
                    text: shareText,
                    url: shareUrl,
                }).catch(error => {
                    console.warn('Error sharing:', error);
                    showShareFallback(this, tourTitle, shareUrl);
                });
            } else {
                showShareFallback(this, tourTitle, shareUrl);
            }
        });
    });
    
    // Initialize view more button
    const loadMoreBtn = document.querySelector('.trending-load-more');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // You can implement AJAX loading of more tours here
            // For now, let's just show a message
            const icon = this.querySelector('i');
            this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading...';
            
            // Simulate loading more content
            setTimeout(() => {
                // Reset button text after "loading"
                this.innerHTML = 'View All Destinations <i class="fa fa-arrow-right"></i>';
                
                // Show notification
                showNotification('More destinations coming soon!', 'info');
            }, 1500);
        });
    }
    
    // Initialize hover effect for cards
    trendingCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            const image = this.querySelector('.trending-card-image img');
            if (image) {
                image.style.transform = 'scale(1.05)';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            const image = this.querySelector('.trending-card-image img');
            if (image) {
                image.style.transform = 'scale(1)';
            }
        });
    });
}

// Animation functions
function animateCardIn(card) {
    card.style.display = 'flex';
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, 50);
}

function animateCardOut(card) {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        card.style.display = 'none';
    }, 300);
}

function animateHeart(button) {
    // Create and append heart elements for animation
    for (let i = 0; i < 5; i++) {
        const heart = document.createElement('span');
        heart.classList.add('heart-particle');
        heart.innerHTML = '<i class="fa fa-heart"></i>';
        heart.style.top = '50%';
        heart.style.left = '50%';
        heart.style.position = 'absolute';
        heart.style.color = '#FF5722';
        heart.style.transform = 'translate(-50%, -50%)';
        heart.style.opacity = '1';
        heart.style.fontSize = `${Math.random() * 10 + 5}px`;
        heart.style.zIndex = '10';
        button.appendChild(heart);
        
        // Animate the heart
        const angle = Math.random() * Math.PI * 2;
        const distance = Math.random() * 60 + 20;
        const x = Math.cos(angle) * distance;
        const y = Math.sin(angle) * distance;
        
        // Using GSAP would be ideal, but we'll use CSS animations
        heart.style.transition = 'all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        setTimeout(() => {
            heart.style.transform = `translate(calc(-50% + ${x}px), calc(-50% + ${y}px))`;
            heart.style.opacity = '0';
        }, 50);
        
        // Remove the heart element after animation
        setTimeout(() => {
            heart.remove();
        }, 800);
    }
}

function showShareFallback(button, title, url) {
    // Create a simple fallback for sharing
    // You can implement a custom share dialog here
    
    // Create temporary input for copy to clipboard
    const input = document.createElement('input');
    input.value = url;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    
    // Show notification
    showNotification('Link copied to clipboard!', 'success');
    
    // Animate button
    button.classList.add('share-active');
    setTimeout(() => {
        button.classList.remove('share-active');
    }, 1000);
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.classList.add('toast-notification', `toast-${type}`);
    notification.innerHTML = `
        <div class="toast-icon">
            <i class="fa fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        </div>
        <div class="toast-message">${message}</div>
    `;
    
    // Add to the DOM
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Animate out and remove
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Function to initialize number input components
function initNumberInputs() {
    const numberInputs = document.querySelectorAll('.number-input');
    
    if (numberInputs.length === 0) return;
    
    numberInputs.forEach(container => {
        const input = container.querySelector('input[type="number"]');
        const minusBtn = container.querySelector('.minus-btn');
        const plusBtn = container.querySelector('.plus-btn');
        
        if (!input || !minusBtn || !plusBtn) return;
        
        // Set initial state of minus button
        updateButtonState(input, minusBtn, plusBtn);
        
        // Add event listeners
        minusBtn.addEventListener('click', () => {
            const currentValue = parseInt(input.value);
            const minValue = parseInt(input.min);
            
            if (currentValue > minValue) {
                input.value = currentValue - 1;
                updateButtonState(input, minusBtn, plusBtn);
                
                // Trigger change event for any listeners
                const event = new Event('change', { bubbles: true });
                input.dispatchEvent(event);
            }
        });
        
        plusBtn.addEventListener('click', () => {
            const currentValue = parseInt(input.value);
            const maxValue = parseInt(input.max);
            
            if (currentValue < maxValue) {
                input.value = currentValue + 1;
                updateButtonState(input, minusBtn, plusBtn);
                
                // Trigger change event for any listeners
                const event = new Event('change', { bubbles: true });
                input.dispatchEvent(event);
            }
        });
        
        // Update on manual input
        input.addEventListener('change', () => {
            updateButtonState(input, minusBtn, plusBtn);
        });
    });
}

// Function to update button states based on input value
function updateButtonState(input, minusBtn, plusBtn) {
    const currentValue = parseInt(input.value);
    const minValue = parseInt(input.min);
    const maxValue = parseInt(input.max);
    
    // Disable minus button if at minimum value
    if (currentValue <= minValue) {
        minusBtn.classList.add('disabled');
        minusBtn.disabled = true;
    } else {
        minusBtn.classList.remove('disabled');
        minusBtn.disabled = false;
    }
    
    // Disable plus button if at maximum value
    if (currentValue >= maxValue) {
        plusBtn.classList.add('disabled');
        plusBtn.disabled = true;
    } else {
        plusBtn.classList.remove('disabled');
        plusBtn.disabled = false;
    }
}

// Add dynamic elements and enhanced interaction to tour cards
function initTourCards() {
    const tourCards = document.querySelectorAll('.tour-card');
    
    tourCards.forEach(card => {
        // Add favorite button to each card
        const imageContainer = card.querySelector('.tour-image');
        if (imageContainer && !imageContainer.querySelector('.card-favorite')) {
            const favoriteBtn = document.createElement('div');
            favoriteBtn.className = 'card-favorite';
            favoriteBtn.innerHTML = '<i class="far fa-heart"></i>';
            imageContainer.appendChild(favoriteBtn);
            
            // Add click functionality
            favoriteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.toggle('active');
                
                const icon = this.querySelector('i');
                if (this.classList.contains('active')) {
                    icon.className = 'fas fa-heart';
                    // Optional: Show feedback to user
                    showToast('Added to favorites!');
                } else {
                    icon.className = 'far fa-heart';
                    // Optional: Show feedback to user
                    showToast('Removed from favorites.');
                }
            });
        }
        
        // Enhance view details button with animated icon
        const viewDetailsBtn = card.querySelector('.view-details-btn');
        if (viewDetailsBtn && !viewDetailsBtn.querySelector('i')) {
            viewDetailsBtn.innerHTML += ' <i class="fas fa-arrow-right"></i>';
        }
        
        // Make entire card clickable
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on the favorite button
            if (!e.target.closest('.card-favorite')) {
                const detailsBtn = this.querySelector('.view-details-btn');
                if (detailsBtn) {
                    const tourName = this.querySelector('h3').textContent;
                    // Show an animation on the button when clicked
                    detailsBtn.classList.add('clicked');
                    setTimeout(() => {
                        detailsBtn.classList.remove('clicked');
                        // Then navigate or show modal
                        alert(`You will be redirected to ${tourName} details page.`);
                    }, 300);
                }
            }
        });
    });
}

// Simple toast notification function
function showToast(message) {
    // Check if a toast container already exists
    let toastContainer = document.querySelector('.toast-container');
    
    // If not, create one
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
        
        // Add some basic styles to the toast container
        toastContainer.style.position = 'fixed';
        toastContainer.style.bottom = '20px';
        toastContainer.style.right = '20px';
        toastContainer.style.zIndex = '9999';
    }
    
    // Create a new toast
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = message;
    
    // Style the toast
    toast.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
    toast.style.color = 'white';
    toast.style.padding = '10px 15px';
    toast.style.borderRadius = '4px';
    toast.style.marginTop = '10px';
    toast.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.2)';
    toast.style.transition = 'all 0.3s ease';
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(20px)';
    
    // Add to container
    toastContainer.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        
        // Remove from DOM after animation completes
        setTimeout(() => {
            toastContainer.removeChild(toast);
            
            // If no more toasts, remove the container
            if (toastContainer.children.length === 0) {
                document.body.removeChild(toastContainer);
            }
        }, 300);
    }, 3000);
} 
