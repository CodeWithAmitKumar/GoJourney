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
});

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
