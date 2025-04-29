// Enhanced Dashboard Functionality

document.addEventListener('DOMContentLoaded', function() {
    // Preload destination images
    preloadDestinationImages();
    
    // Ensure dark mode is consistent
    ensureDarkModeConsistency();
    
    // Testimonial slider improvements
    improveTestimonialSlider();
    
    // Travel card image gallery improvements
    improveImageGallery();
    
    // Handle missing images with fallbacks
    handleMissingImages();
    
    // Fix image gallery for cards with broken images
    fixImageGallery();
    
    // Add responsive functionality to forms
    enhanceFormFields();
    
    // Improve booking card transitions
    improveBookingCards();
    
    // Add search functionality
    enhanceSearchExperience();
    
    // Enhance footer
    enhanceFooter();
    
    // Add animation classes to elements when they come into view
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.booking-card, .travel-card, .testimonial-card, .booking-section-title');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementPosition < windowHeight - 100) {
                element.classList.add('animate-in');
            }
        });
    };
    
    // Call on initial load and scroll
    animateOnScroll();
    window.addEventListener('scroll', animateOnScroll);
    
    // Enhanced hover effects for cards
    const cards = document.querySelectorAll('.booking-card, .travel-card, .testimonial-card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.classList.add('card-hover');
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('card-hover');
        });
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Enhanced form interactions
    const formControls = document.querySelectorAll('.form-control');
    
    formControls.forEach(input => {
        // Add focus class to parent when input is focused
        input.addEventListener('focus', function() {
            this.closest('.form-group').classList.add('input-focused');
        });
        
        input.addEventListener('blur', function() {
            this.closest('.form-group').classList.remove('input-focused');
        });
        
        // Validate date inputs to ensure return date is after departure date
        if (input.id === 'flight-depart' || input.id === 'hotel-check-in') {
            input.addEventListener('change', function() {
                const returnInput = document.getElementById(this.id === 'flight-depart' ? 'flight-return' : 'hotel-check-out');
                if (returnInput && returnInput.value) {
                    const departDate = new Date(this.value);
                    const returnDate = new Date(returnInput.value);
                    
                    if (departDate > returnDate) {
                        // Set return date to depart date + 1 day
                        const nextDay = new Date(departDate);
                        nextDay.setDate(nextDay.getDate() + 1);
                        returnInput.valueAsDate = nextDay;
                    }
                }
            });
        }
    });
    
    // Enhanced button hover effects
    const buttons = document.querySelectorAll('.booking-search-btn, .card-buttons button');
    
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.classList.add('btn-hover');
        });
        
        button.addEventListener('mouseleave', function() {
            this.classList.remove('btn-hover');
        });
    });
    
    // Enhance image galleries in travel cards
    const travelCards = document.querySelectorAll('.travel-card');
    
    travelCards.forEach(card => {
        const imageContainer = card.querySelector('.image-container');
        const images = card.querySelectorAll('.image-container img');
        const dots = card.querySelectorAll('.image-dot');
        const prevBtn = card.querySelector('.image-nav.prev');
        const nextBtn = card.querySelector('.image-nav.next');
        
        if (!imageContainer || images.length <= 1) return;
        
        let currentImage = 0;
        
        // Setup initial state - make sure all images except first are hidden
        images.forEach((img, idx) => {
            img.style.display = idx === 0 ? 'block' : 'none';
        });
        
        // Update dots for initial state
        dots.forEach((dot, idx) => {
            dot.classList.toggle('active', idx === 0);
        });
        
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
            // Hide all images first
            images.forEach(img => {
                img.style.display = 'none';
            });
            
            // Show only the current image
            if (images[index]) {
                images[index].style.display = 'block';
            }
            
            // Update dots
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });
        }
    });
    
    // Add floating effect to search button
    const searchBtns = document.querySelectorAll('.booking-search-btn');
    
    searchBtns.forEach(btn => {
        setInterval(() => {
            btn.classList.toggle('float');
        }, 2000);
    });
    
    // Add date validation to ensure check-out is after check-in
    const checkInInputs = document.querySelectorAll('#hotel-check-in, #flight-depart');
    
    checkInInputs.forEach(input => {
        input.addEventListener('change', function() {
            const checkOutId = this.id === 'hotel-check-in' ? 'hotel-check-out' : 'flight-return';
            const checkOutInput = document.getElementById(checkOutId);
            
            if (checkOutInput) {
                const minDate = new Date(this.value);
                minDate.setDate(minDate.getDate() + 1);
                
                // Format date to YYYY-MM-DD
                const month = String(minDate.getMonth() + 1).padStart(2, '0');
                const day = String(minDate.getDate()).padStart(2, '0');
                const formattedDate = `${minDate.getFullYear()}-${month}-${day}`;
                
                checkOutInput.min = formattedDate;
                
                // If check-out date is before check-in date, update it
                if (new Date(checkOutInput.value) <= new Date(this.value)) {
                    checkOutInput.value = formattedDate;
                }
            }
        });
    });
    
    // Initialize today's date as minimum for date inputs
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    const formattedToday = `${today.getFullYear()}-${month}-${day}`;
    
    dateInputs.forEach(input => {
        input.min = formattedToday;
        
        // Set default values if not already set
        if (!input.value) {
            if (input.id === 'hotel-check-in' || input.id === 'flight-depart' || input.id === 'train-date') {
                input.value = formattedToday;
            } else if (input.id === 'hotel-check-out' || input.id === 'flight-return') {
                const tomorrow = new Date();
                tomorrow.setDate(today.getDate() + 1);
                const tomorrowMonth = String(tomorrow.getMonth() + 1).padStart(2, '0');
                const tomorrowDay = String(tomorrow.getDate()).padStart(2, '0');
                input.value = `${tomorrow.getFullYear()}-${tomorrowMonth}-${tomorrowDay}`;
            }
        }
    });
    
    // Add typewriter effect to welcome message
    const welcomeSubtitle = document.querySelector('.welcome-subtitle');
    if (welcomeSubtitle) {
        const text = welcomeSubtitle.textContent;
        welcomeSubtitle.textContent = '';
        let charIndex = 0;
        
        function typeWriter() {
            if (charIndex < text.length) {
                welcomeSubtitle.textContent += text.charAt(charIndex);
                charIndex++;
                setTimeout(typeWriter, 20);
            }
        }
        
        // Start typing after a short delay
        setTimeout(typeWriter, 500);
    }
    
    // Add wishlist functionality to travel cards
    enhanceWishlistFunctionality();
});

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
        
        // Setup initial state - make sure all images except first are hidden
        images.forEach((img, idx) => {
            img.style.display = idx === 0 ? 'block' : 'none';
        });
        
        // Update dots for initial state
        dots.forEach((dot, idx) => {
            dot.classList.toggle('active', idx === 0);
        });
        
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
            // Hide all images first
            images.forEach(img => {
                img.style.display = 'none';
            });
            
            // Show only the current image
            if (images[index]) {
                images[index].style.display = 'block';
            }
            
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

// Prevent multiple toast notifications
let activeToastTimeout = null;

// Completely redesigned toast notification function
function showToast(message, isAddAction = false) {
    console.log('Showing toast:', { message, isAddAction }); // Debug log
    
    // Clear any existing toast before showing a new one
    clearExistingToasts();
    
    // Check if toast container exists
    let toastContainer = document.querySelector('.toast-container');
    
    // Create toast container if it doesn't exist
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.classList.add('toast-container');
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element with very clear visual difference
    const toast = document.createElement('div');
    toast.classList.add('toast');
    
    // Explicitly set class based on action type to ensure correct styling
    if (isAddAction) {
        toast.classList.add('toast-add');
        toast.classList.remove('toast-remove');
    } else {
        toast.classList.add('toast-remove');
        toast.classList.remove('toast-add');
    }
    
    // Create toast icon with clear indication of action type
    const iconDiv = document.createElement('div');
    iconDiv.classList.add('toast-icon');
    
    // Set icon based explicitly on action type
    if (isAddAction) {
        iconDiv.innerHTML = '<i class="fas fa-heart"></i>';
    } else {
        iconDiv.innerHTML = '<i class="fas fa-times"></i>';
    }
    
    toast.appendChild(iconDiv);
    
    // Create toast content with the message
    const contentDiv = document.createElement('div');
    contentDiv.classList.add('toast-content');
    contentDiv.textContent = message;
    toast.appendChild(contentDiv);
    
    // Create close button
    const closeBtn = document.createElement('button');
    closeBtn.classList.add('toast-close');
    closeBtn.innerHTML = '<i class="fas fa-times"></i>';
    closeBtn.addEventListener('click', () => {
        closeToast(toast);
    });
    toast.appendChild(closeBtn);
    
    // Add toast to container
    toastContainer.appendChild(toast);
    
    // Add debugging class to help identify in browser
    toast.classList.add(isAddAction ? 'debug-add-toast' : 'debug-remove-toast');
    
    // Trigger animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Auto close after 4 seconds
    activeToastTimeout = setTimeout(() => {
        closeToast(toast);
    }, 4000);
    
    // Pause timer on hover
    toast.addEventListener('mouseenter', () => {
        clearTimeout(activeToastTimeout);
    });
    
    // Resume timer on mouse leave
    toast.addEventListener('mouseleave', () => {
        activeToastTimeout = setTimeout(() => {
            closeToast(toast);
        }, 2000);
    });
}

// Clear any existing toasts
function clearExistingToasts() {
    // Clear the active timeout if it exists
    if (activeToastTimeout) {
        clearTimeout(activeToastTimeout);
        activeToastTimeout = null;
    }
    
    // Remove any existing toasts
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => {
        toast.remove();
    });
    
    // Remove the container if it's empty
    const toastContainer = document.querySelector('.toast-container');
    if (toastContainer && toastContainer.children.length === 0) {
        toastContainer.remove();
    }
}

// Close toast with animation
function closeToast(toast) {
    // Clear active timeout
    if (activeToastTimeout) {
        clearTimeout(activeToastTimeout);
        activeToastTimeout = null;
    }
    
    // Add slide out animation
    toast.classList.remove('show');
    
    // Remove element after animation completes
    setTimeout(() => {
        toast.remove();
        
        // Remove container if empty
        const toastContainer = document.querySelector('.toast-container');
        if (toastContainer && toastContainer.children.length === 0) {
            toastContainer.remove();
        }
    }, 400); // Match the animation duration
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

// Update the wishlist functionality for improved animation
function enhanceWishlistFunctionality() {
    const wishlistButtons = document.querySelectorAll('.wishlist-btn');
    
    wishlistButtons.forEach(button => {
        // Check if the item is already in the wishlist (could be stored in localStorage)
        const card = button.closest('.travel-card');
        const cardId = card.dataset.category || `card-${Math.random().toString(36).substr(2, 9)}`;
        
        // Make sure the card has a data attribute for identification
        if (!card.dataset.category) {
            card.dataset.category = cardId;
        }
        
        const isInWishlist = localStorage.getItem(`wishlist_${cardId}`) === 'true';
        
        // Set initial state
        updateWishlistButtonState(button, isInWishlist);
        
        button.addEventListener('click', function(e) {
            // Prevent multiple rapid clicks
            e.preventDefault();
            
            if (this.disabled) {
                return;
            }
            
            // Disable briefly to prevent double-clicks
            this.disabled = true;
            
            const card = this.closest('.travel-card');
            const cardId = card.dataset.category;
            const cardTitle = card.querySelector('.card-title').textContent;
            const isCurrentlyInWishlist = this.classList.contains('active');
            
            // Toggle wishlist state
            const newWishlistState = !isCurrentlyInWishlist;
            
            // Update localStorage BEFORE updating UI
            localStorage.setItem(`wishlist_${cardId}`, newWishlistState ? 'true' : 'false');
            
            // Show correct toast notification based on the new state
            if (newWishlistState) {
                // Adding to wishlist
                showToast(`${cardTitle} added to your wishlist`, true);
                showWishlistAnimation(card);
            } else {
                // Removing from wishlist
                showToast(`${cardTitle} removed from your wishlist`, false);
            }
            
            // Update button state AFTER toast is shown
            updateWishlistButtonState(this, newWishlistState);
            
            // Re-enable button after a short delay
            setTimeout(() => {
                this.disabled = false;
            }, 500);
        });
    });
}

// Update wishlist button state
function updateWishlistButtonState(button, isInWishlist) {
    if (isInWishlist) {
        button.classList.add('active');
        button.innerHTML = '<i class="fas fa-heart"></i> Wishlisted';
    } else {
        button.classList.remove('active');
        button.innerHTML = '<i class="far fa-heart"></i> Wishlist';
    }
}

// Show wishlist animation
function showWishlistAnimation(card) {
    // Create container for heart animations if it doesn't exist
    let animationContainer = card.querySelector('.heart-animation-container');
    if (!animationContainer) {
        animationContainer = document.createElement('div');
        animationContainer.classList.add('heart-animation-container');
        card.appendChild(animationContainer);
    } else {
        // Clear existing animations
        animationContainer.innerHTML = '';
    }
    
    // Add main heart animation
    const heart = document.createElement('div');
    heart.classList.add('heart-animation');
    animationContainer.appendChild(heart);
    
    // Add floating hearts
    createFloatingHearts(animationContainer, 6);
    
    // Remove after animation completes
    setTimeout(() => {
        animationContainer.remove();
    }, 1500);
}

// Create multiple small floating hearts
function createFloatingHearts(container, count) {
    for (let i = 0; i < count; i++) {
        const heart = document.createElement('div');
        heart.classList.add('floating-heart');
        
        // Randomize starting position
        const tx = (Math.random() * 60 - 30) + 'px';
        const ty = (Math.random() * 20) + 'px';
        const rotation = (Math.random() * 60 - 30) + 'deg';
        
        // Set CSS variables for animation
        heart.style.setProperty('--tx', tx);
        heart.style.setProperty('--ty', ty);
        heart.style.setProperty('--r', rotation);
        
        // Randomize animation delay
        heart.style.animationDelay = (Math.random() * 0.3) + 's';
        
        // Position at center initially
        heart.style.top = '50%';
        heart.style.left = '50%';
        
        container.appendChild(heart);
    }
}

// Handle missing images with fallbacks
function handleMissingImages() {
    // Get all images in travel cards
    const cardImages = document.querySelectorAll('.travel-card .image-container img');
    
    // Use local destination images as placeholders
    const placeholders = [
        'images/destinations/chilika.jpg',
        'images/destinations/puri.jpg',
        'images/destinations/konark.jpg',
        'images/destinations/lingaraj.jpg',
        'images/destinations/tarinitemple.jpg',
        'images/destinations/shreeRam.jpg',
        'images/destinations/barheipani.png',
        'images/destinations/tunki.jpg',
        'images/destinations/itr.webp'
    ];
    
    // Add error handling to each image
    cardImages.forEach((img, index) => {
        // Set a fallback for images that fail to load
        img.onerror = function() {
            console.log('Image failed to load:', this.src);
            
            // Add fallback class
            this.classList.add('fallback-img');
            
            // Use a different destination image as fallback
            const placeholderIndex = index % placeholders.length;
            this.src = placeholders[placeholderIndex];
            
            // If even the placeholder destination image fails, use a data URI as final fallback
            this.onerror = function() {
                console.log('Fallback image also failed to load:', this.src);
                this.src = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22600%22%20height%3D%22400%22%20viewBox%3D%220%200%20600%20400%22%3E%3Crect%20width%3D%22600%22%20height%3D%22400%22%20fill%3D%22%23f0f2f5%22%2F%3E%3Ctext%20x%3D%22300%22%20y%3D%22200%22%20font-family%3D%22Arial%2C%20sans-serif%22%20font-size%3D%2218%22%20fill%3D%22%23999%22%20text-anchor%3D%22middle%22%20dominant-baseline%3D%22middle%22%3EDestination%20Image%3C%2Ftext%3E%3C%2Fsvg%3E';
            };
        };
        
        // Force error handling for already loaded broken images
        if (img.complete && (img.naturalWidth === 0 || img.naturalHeight === 0)) {
            const originalSrc = img.src;
            img.src = '';  // Reset src to trigger error
            console.log('Fixing already broken image:', originalSrc);
            img.onerror();  // Call the error handler manually
        }
    });
    
    // Fix image gallery functionality for cards with broken images
    fixImageGallery();
}

// Fix image gallery for cards with broken images
function fixImageGallery() {
    const travelCards = document.querySelectorAll('.travel-card');
    
    travelCards.forEach(card => {
        const imageContainer = card.querySelector('.image-container');
        const dots = card.querySelectorAll('.image-dot');
        
        if (!imageContainer || !dots.length) return;
        
        // Ensure at least one image is showing
        let visibleImages = 0;
        const images = imageContainer.querySelectorAll('img');
        
        images.forEach(img => {
            if (!img.classList.contains('fallback-img')) {
                visibleImages++;
            }
        });
        
        // If all images are broken, make sure at least the first one is visible
        if (visibleImages === 0 && images.length > 0) {
            images[0].style.display = 'block';
            
            // Make sure only the first dot is active
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === 0);
            });
            
            // Reset container position
            imageContainer.style.transform = 'translateX(0)';
        }
    });
}

// Function to preload all destination images
function preloadDestinationImages() {
    const imagePaths = [
        'images/destinations/chilika.jpg',
        'images/destinations/puri.jpg',
        'images/destinations/konark.jpg',
        'images/destinations/lingaraj.jpg',
        'images/destinations/tarinitemple.jpg',
        'images/destinations/shreeRam.jpg',
        'images/destinations/barheipani.png',
        'images/destinations/tunki.jpg',
        'images/destinations/itr.webp',
        'images/destinations/chandrabhaga.jpg'
    ];
    
    // Preload each image
    imagePaths.forEach(path => {
        const img = new Image();
        img.src = path;
        console.log('Preloading image:', path);
    });
}

// Function to ensure dark mode consistency (both dark-theme and dark-mode classes work)
function ensureDarkModeConsistency() {
    // Check if either dark class is present
    const isDarkTheme = document.body.classList.contains('dark-theme');
    const isDarkMode = document.body.classList.contains('dark-mode');
    
    // If one is enabled but not the other, synchronize them
    if (isDarkTheme && !isDarkMode) {
        document.body.classList.add('dark-mode');
    } else if (isDarkMode && !isDarkTheme) {
        document.body.classList.add('dark-theme');
    }
    
    // Add dark mode class to specific elements that need it
    if (isDarkTheme || isDarkMode) {
        const travelCards = document.querySelectorAll('.travel-card');
        travelCards.forEach(card => {
            card.classList.add('dark-mode-card');
        });
        
        const travelExplorer = document.querySelector('.travel-explorer-section');
        if (travelExplorer) {
            travelExplorer.classList.add('dark-mode-section');
        }
    }
} 