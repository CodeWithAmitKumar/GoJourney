// Add scrolled class to navbar when scrolling
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Simple function to show a toast notification
function showToast(message, duration = 2000) {
    // Get the toast container
    const toastContainer = document.getElementById('toast-container');
    
    // Check if there's already a toast notification
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.textContent = message;
    
    // Add toast to container
    toastContainer.appendChild(toast);
    
    // Force a reflow to ensure the transition works
    void toast.offsetWidth;
    
    // Make the toast visible
    toast.classList.add('show');
    
    // Remove the toast after duration
    setTimeout(() => {
        toast.classList.remove('show');
        
        // Remove from DOM after fade out
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 400);
    }, duration);
}

// Make the function globally accessible
window.showToast = showToast;

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded');
    
    // Get references to elements
    const loginModal = document.getElementById('loginModal');
    const signupModal = document.getElementById('signupModal');
    const loginBtn = document.getElementById('loginBtn');
    const signupBtn = document.getElementById('signupBtn');
    const closeBtns = document.querySelectorAll('.close-btn');
    const showLoginLink = document.getElementById('showLogin');
    const showSignupLink = document.getElementById('showSignup');
    const exploreMoreBtn = document.getElementById('exploreMoreBtn');
    
    console.log('Explore More Button:', exploreMoreBtn);
    
    // Function to open a modal
    function openModal(modal) {
        if (modal) {
            modal.style.display = 'flex';
        }
    }
    
    // Function to close a modal
    function closeModal(modal) {
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    // Set up button event listeners
    if (loginBtn) {
        loginBtn.addEventListener('click', function() {
            openModal(loginModal);
        });
    }
    
    if (signupBtn) {
        signupBtn.addEventListener('click', function() {
            openModal(signupModal);
        });
    }
    
    // Close buttons
    closeBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            closeModal(loginModal);
            closeModal(signupModal);
        });
    });
    
    // Switch between forms
    if (showLoginLink) {
        showLoginLink.addEventListener('click', function(e) {
            e.preventDefault();
            closeModal(signupModal);
            openModal(loginModal);
        });
    }
    
    if (showSignupLink) {
        showSignupLink.addEventListener('click', function(e) {
            e.preventDefault();
            closeModal(loginModal);
            openModal(signupModal);
        });
    }
    
    // Close when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === loginModal) closeModal(loginModal);
        if (e.target === signupModal) closeModal(signupModal);
    });
    
    // Special handling for Explore More button
    if (exploreMoreBtn) {
        exploreMoreBtn.onclick = function(e) {
            e.preventDefault();
            console.log('Explore More clicked');
            showToast('Login to explore more places');
            
            // Redirect to login after 2 seconds
            setTimeout(function() {
                openModal(loginModal);
            }, 2000);
            
            return false;
        };
    }
    
    // Form validation
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            // Form will submit to auth/login.php
        });
    }
    
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            const password = document.getElementById('signup-password').value;
            const confirmPassword = document.getElementById('signup-confirm').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    }
    
    // Test toast on page load
    setTimeout(function() {
        showToast('Page loaded successfully');
    }, 1000);
}); 