/**
 * Flight booking form handling
 * Manages the flight booking form behavior
 */

document.addEventListener('DOMContentLoaded', function() {
    // Flight type selection handling (One Way / Round Trip)
    const oneWayRadio = document.getElementById('one-way');
    const roundTripRadio = document.getElementById('round-trip');
    const returnDateContainer = document.querySelector('.return-date-container');
    const returnDateInput = document.getElementById('flight-return');
    
    // Set up event listeners for flight type selection
    if (oneWayRadio && roundTripRadio && returnDateContainer) {
        // Initial state setup
        if (oneWayRadio.checked) {
            returnDateContainer.style.display = 'none';
            returnDateInput.removeAttribute('required');
            returnDateInput.value = '';
        }
        
        // One-way selection
        oneWayRadio.addEventListener('change', function() {
            if (this.checked) {
                returnDateContainer.style.display = 'none';
                returnDateInput.removeAttribute('required');
                returnDateInput.value = '';
            }
        });
        
        // Round-trip selection
        roundTripRadio.addEventListener('change', function() {
            if (this.checked) {
                returnDateContainer.style.display = 'block';
                returnDateInput.setAttribute('required', 'required');
                
                // Set minimum date for return (must be after departure)
                const departDate = document.getElementById('flight-depart').value;
                if (departDate) {
                    returnDateInput.min = departDate;
                    
                    // Set default return date to one week after departure if not set
                    if (!returnDateInput.value) {
                        const defaultReturn = new Date(departDate);
                        defaultReturn.setDate(defaultReturn.getDate() + 7);
                        returnDateInput.value = defaultReturn.toISOString().split('T')[0];
                    }
                }
            }
        });
        
        // Ensure return date is after departure date
        const departureDateInput = document.getElementById('flight-depart');
        if (departureDateInput) {
            departureDateInput.addEventListener('change', function() {
                if (roundTripRadio.checked) {
                    const departDate = this.value;
                    returnDateInput.min = departDate;
                    
                    // If return date is before departure date, update it
                    if (returnDateInput.value && returnDateInput.value < departDate) {
                        returnDateInput.value = departDate;
                    }
                }
            });
        }
    }
    
    // Update search button behavior for flight search
    const flightSearchBtn = document.querySelector('.flight-card .booking-search-btn');
    const flightForm = document.querySelector('.flight-card .booking-card-form');
    
    if (flightSearchBtn && flightForm) {
        flightSearchBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Basic form validation
            const fromCity = document.getElementById('flight-from').value;
            const toCity = document.getElementById('flight-to').value;
            const departDate = document.getElementById('flight-depart').value;
            
            // Check required fields
            if (!fromCity || !toCity || !departDate) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Check if round trip is selected but return date is not set
            if (roundTripRadio.checked && !returnDateInput.value) {
                alert('Please select a return date for round trip booking');
                return;
            }
            
            // Build the URL for flight results page
            let url = 'flight_results.php?from=' + encodeURIComponent(fromCity) + 
                      '&to=' + encodeURIComponent(toCity) + 
                      '&depart=' + encodeURIComponent(departDate);
            
            // Add return date if it's a round trip
            if (roundTripRadio.checked) {
                url += '&return=' + encodeURIComponent(returnDateInput.value);
            }
            
            // Add passengers and class
            const passengers = document.getElementById('flight-passengers').value;
            const flightClass = document.getElementById('flight-class').value;
            
            url += '&passengers=' + encodeURIComponent(passengers) + 
                   '&class=' + encodeURIComponent(flightClass) +
                   '&type=' + (roundTripRadio.checked ? 'round-trip' : 'one-way');
            
            // Redirect to flight results page
            window.location.href = url;
        });
    }
}); 