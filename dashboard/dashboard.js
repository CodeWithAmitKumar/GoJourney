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
}); 
