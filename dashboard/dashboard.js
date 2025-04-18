// Theme toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    const moonIcon = themeToggle.querySelector('.fa-moon');
    const sunIcon = themeToggle.querySelector('.fa-sun');
    const welcomeMessage = document.querySelector('.welcome-message');
    
    // Store the original welcome message text
    const originalWelcomeText = welcomeMessage ? welcomeMessage.innerHTML : '';
    
    // Function to ensure welcome message is visible and preserved
    function preserveWelcomeMessage() {
        if (welcomeMessage) {
            welcomeMessage.style.display = 'block';
            welcomeMessage.style.visibility = 'visible';
            welcomeMessage.style.opacity = '1';
            
            // Apply gradient text in dark mode or regular color in light mode
            if (document.body.classList.contains('dark-theme')) {
                // CSS will handle gradient styling through the class
                welcomeMessage.style.color = ''; // Remove inline color to let CSS handle it
            } else {
                // Reset to black for light mode
                welcomeMessage.style.background = '';
                welcomeMessage.style.webkitBackgroundClip = '';
                welcomeMessage.style.webkitTextFillColor = '';
                welcomeMessage.style.backgroundClip = '';
                welcomeMessage.style.textFillColor = '';
                welcomeMessage.style.color = '#333';
            }
            
            // Ensure the content hasn't been lost
            if (welcomeMessage.innerHTML.trim() === '') {
                welcomeMessage.innerHTML = originalWelcomeText;
            }
        }
    }
    
    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
        moonIcon.style.display = 'none';
        sunIcon.style.display = 'block';
        preserveWelcomeMessage();
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
        
        // Always preserve welcome message
        preserveWelcomeMessage();
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

    // Function to perform search (placeholder for now)
    function performSearch() {
        const searchTerm = searchInput.value.trim();
        if (searchTerm) {
            console.log('Searching for:', searchTerm);
            // Here you would implement the actual search functionality
            // For example: window.location.href = 'search-results.php?q=' + encodeURIComponent(searchTerm);
        }
    }
    
    // Auto-dismiss notifications after 3 seconds
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
            }, 3000); // Wait 3 seconds before starting to fade out
        });
    }
}); 
