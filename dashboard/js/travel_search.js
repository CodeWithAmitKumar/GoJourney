/**
 * GoJourney Travel Search JS
 * Handles train and flight search functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Set minimum dates for all date inputs to today
    const today = new Date().toISOString().split('T')[0];
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.setAttribute('min', today);
    });
    
    // Initialize the train search form
    initTrainSearch();
    
    // Initialize the flight search form
    initFlightSearch();
    
    // Initialize the hotel search form
    initHotelSearch();

    // Train search form handling
    const trainSearchForm = document.querySelector('.train-card .booking-card-form');
    if (trainSearchForm) {
        trainSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            searchTrains();
        });

        // Add event listener to the search button
        const trainSearchBtn = document.querySelector('.train-card .booking-search-btn');
        if (trainSearchBtn) {
            trainSearchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                searchTrains();
            });
        }
    }

    // Flight search form handling
    const flightSearchForm = document.querySelector('.flight-card .booking-card-form');
    if (flightSearchForm) {
        flightSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            searchFlights();
        });

        // Add event listener to the search button
        const flightSearchBtn = document.querySelector('.flight-card .booking-search-btn');
        if (flightSearchBtn) {
            flightSearchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                searchFlights();
            });
        }
    }

    // Helper function to show loading state
    function showLoading(formElement) {
        const searchBtn = formElement.querySelector('.booking-search-btn');
        if (searchBtn) {
            searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
            searchBtn.disabled = true;
        }
    }

    // Helper function to hide loading state
    function hideLoading(formElement, originalText) {
        const searchBtn = formElement.querySelector('.booking-search-btn');
        if (searchBtn) {
            searchBtn.innerHTML = originalText || 'Search <i class="fas fa-search"></i>';
            searchBtn.disabled = false;
        }
    }

    // Helper function to display error message
    function showError(container, message) {
        // Remove existing error messages
        const existingErrors = container.querySelectorAll('.search-error');
        existingErrors.forEach(error => error.remove());

        // Create and append error message
        const errorElement = document.createElement('div');
        errorElement.className = 'search-error';
        errorElement.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        container.appendChild(errorElement);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            errorElement.remove();
        }, 5000);
    }

    // Function to search for trains
    function searchTrains() {
        const trainForm = document.querySelector('.train-card .booking-card-form');
        const trainResultsContainer = document.querySelector('.train-card .search-results') || 
                                     createResultsContainer('.train-card');
        
        const fromStation = document.getElementById('train-from').value;
        const toStation = document.getElementById('train-to').value;
        const travelDate = document.getElementById('train-date').value;
        const travelClass = document.getElementById('train-class').value;
        const passengers = document.getElementById('train-passengers').value;

        // Validate inputs
        if (!fromStation || !toStation || !travelDate) {
            showError(trainResultsContainer, 'Please fill in all required fields');
            return;
        }

        // Show loading state
        showLoading(trainForm);
        trainResultsContainer.innerHTML = '<div class="loading-results">Searching for trains...</div>';

        // Create form data for the API request
        const formData = new FormData();
        formData.append('from', fromStation);
        formData.append('to', toStation);
        formData.append('date', travelDate);
        formData.append('class', travelClass);
        formData.append('passengers', passengers);

        // Make API request
        fetch('api/train_search.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Hide loading
            hideLoading(trainForm, 'Search Trains <i class="fas fa-search"></i>');
            
            // Display results
            if (data.status === 'success') {
                displayTrainResults(data, trainResultsContainer);
            } else {
                showError(trainResultsContainer, data.message || 'An error occurred while searching for trains');
            }
        })
        .catch(error => {
            console.error('Error searching for trains:', error);
            hideLoading(trainForm, 'Search Trains <i class="fas fa-search"></i>');
            showError(trainResultsContainer, 'Failed to connect to the server. Please try again later.');
        });
    }

    // Function to search for flights
    function searchFlights() {
        const flightForm = document.querySelector('.flight-card .booking-card-form');
        const flightResultsContainer = document.querySelector('.flight-card .search-results') || 
                                      createResultsContainer('.flight-card');
        
        const fromCity = document.getElementById('flight-from').value;
        const toCity = document.getElementById('flight-to').value;
        const departureDate = document.getElementById('flight-depart').value;
        const returnDate = document.getElementById('flight-return').value;
        const travelClass = document.getElementById('flight-class').value;
        const passengers = document.getElementById('flight-passengers').value;

        // Validate inputs
        if (!fromCity || !toCity || !departureDate) {
            showError(flightResultsContainer, 'Please fill in all required fields');
            return;
        }

        // Show loading state
        showLoading(flightForm);
        flightResultsContainer.innerHTML = '<div class="loading-results">Searching for flights...</div>';

        // Create form data for the API request
        const formData = new FormData();
        formData.append('from', fromCity);
        formData.append('to', toCity);
        formData.append('departure', departureDate);
        formData.append('return', returnDate);
        formData.append('class', travelClass);
        formData.append('passengers', passengers);

        // Make API request
        fetch('api/flight_search.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Hide loading
            hideLoading(flightForm, 'Search Flights <i class="fas fa-search"></i>');
            
            // Display results
            if (data.status === 'success') {
                displayFlightResults(data, flightResultsContainer);
            } else {
                showError(flightResultsContainer, data.message || 'An error occurred while searching for flights');
            }
        })
        .catch(error => {
            console.error('Error searching for flights:', error);
            hideLoading(flightForm, 'Search Flights <i class="fas fa-search"></i>');
            showError(flightResultsContainer, 'Failed to connect to the server. Please try again later.');
        });
    }

    // Helper function to create results container
    function createResultsContainer(parentSelector) {
        const parent = document.querySelector(parentSelector);
        const resultsContainer = document.createElement('div');
        resultsContainer.className = 'search-results';
        parent.appendChild(resultsContainer);
        return resultsContainer;
    }

    // Helper function to format date nicely
    function formatDate(dateStr) {
        try {
            const date = new Date(dateStr);
            if (isNaN(date.getTime())) {
                return dateStr; // Return original if invalid
            }
            
            const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
            return date.toLocaleDateString('en-IN', options);
        } catch (e) {
            return dateStr; // Return original on error
        }
    }

    // Function to display train search results
    function displayTrainResults(data, container) {
        if (!data.trains || data.trains.length === 0) {
            container.innerHTML = '<div class="no-results">No trains found for this route. Please try different dates or stations.</div>';
            return;
        }

        let html = `
            <div class="results-header">
                <h3>Available Trains</h3>
                <div class="results-count">${data.count} trains found</div>
            </div>
            <div class="results-list">
        `;

        data.trains.forEach(train => {
            // Get price for selected class or first available class
            const selectedClass = document.getElementById('train-class').value;
            const price = train.price[selectedClass] || Object.values(train.price)[0];

            // Convert availability to appropriate CSS class
            const availabilityClass = train.availability.toLowerCase().replace(/\s+/g, '-');

            html += `
                <div class="result-card">
                    <div class="result-header">
                        <div class="train-name">${train.train_name}</div>
                        <div class="train-number">${train.train_number}</div>
                    </div>
                    <div class="result-details">
                        <div class="journey-times">
                            <div class="departure">
                                <div class="time">${train.departure_time}</div>
                                <div class="station">${train.from_station}</div>
                            </div>
                            <div class="journey-duration">
                                <div class="duration-line"></div>
                                <div class="duration-time">${train.duration}</div>
                                <div class="duration-line"></div>
                            </div>
                            <div class="arrival">
                                <div class="time">${train.arrival_time}</div>
                                <div class="station">${train.to_station}</div>
                            </div>
                        </div>
                        <div class="journey-date">
                            <div class="date-label">Date</div>
                            <div class="date-value">${formatDate(train.date)}</div>
                        </div>
                        <div class="price-availability">
                            <div class="price">₹${price}</div>
                            <div class="availability ${availabilityClass}">${train.availability}</div>
                        </div>
                        <div class="booking-action">
                            <button class="book-now-btn" data-train="${train.train_number}" data-class="${selectedClass}">Book Now</button>
                        </div>
                    </div>
                    <div class="result-footer">
                        <button class="show-details-btn" data-toggle="train-details-${train.train_number}">
                            <span class="show-text">Show Details</span>
                            <span class="hide-text">Hide Details</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="additional-details" id="train-details-${train.train_number}">
                            <div class="details-section">
                                <h4>Available Classes</h4>
                                <div class="class-options">
                                    ${Object.entries(train.price).map(([cls, price]) => `
                                        <div class="class-option ${cls === selectedClass ? 'selected' : ''}">
                                            <div class="class-name">${getClassFullName(cls)}</div>
                                            <div class="class-price">₹${price}</div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                            <div class="details-section">
                                <h4>Amenities</h4>
                                <div class="amenities-list">
                                    <span class="amenity"><i class="fas fa-wifi"></i> WiFi</span>
                                    <span class="amenity"><i class="fas fa-utensils"></i> Food</span>
                                    <span class="amenity"><i class="fas fa-charging-station"></i> Charging</span>
                                    <span class="amenity"><i class="fas fa-bed"></i> Bedding</span>
                                    <span class="amenity"><i class="fas fa-restroom"></i> Clean Toilets</span>
                                    <span class="amenity"><i class="fas fa-fire-extinguisher"></i> Safety Features</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += `</div>`;
        container.innerHTML = html;

        // Add event listeners to show/hide details buttons
        const detailButtons = container.querySelectorAll('.show-details-btn');
        detailButtons.forEach(button => {
            button.addEventListener('click', function() {
                const detailsId = this.getAttribute('data-toggle');
                const detailsSection = document.getElementById(detailsId);
                
                if (detailsSection.style.display === 'block') {
                    detailsSection.style.display = 'none';
                    this.classList.remove('active');
                } else {
                    detailsSection.style.display = 'block';
                    this.classList.add('active');
                }
            });
        });

        // Add event listeners to book buttons
        const bookButtons = container.querySelectorAll('.book-now-btn');
        bookButtons.forEach(button => {
            button.addEventListener('click', function() {
                const trainNumber = this.getAttribute('data-train');
                const travelClass = this.getAttribute('data-class');
                
                showBookingModal('train', trainNumber, travelClass);
            });
        });
    }

    // Function to display flight search results
    function displayFlightResults(data, container) {
        const outboundFlights = data.outbound_flights || [];
        const returnFlights = data.return_flights || [];
        const tripType = data.trip_type;

        if (outboundFlights.length === 0 && returnFlights.length === 0) {
            container.innerHTML = '<div class="no-results">No flights found for this route. Please try different dates or cities.</div>';
            return;
        }

        let html = `
            <div class="results-header">
                <h3>Available Flights</h3>
                <div class="results-count">${data.total_flights} flights found</div>
                <div class="trip-type">${tripType === 'one-way' ? 'One Way' : 'Round Trip'}</div>
            </div>
        `;

        // Outbound flights section
        if (outboundFlights.length > 0) {
            html += `
                <div class="flight-section">
                    <div class="section-label">Outbound Flights (${outboundFlights.length})</div>
                    <div class="results-list">
            `;

            outboundFlights.forEach(flight => {
                const selectedClass = document.getElementById('flight-class').value;
                const price = flight.prices[selectedClass];
                const stopsLabel = flight.stops === 0 ? 'Non-stop' : 
                                  flight.stops === 1 ? '1 Stop' : 
                                  `${flight.stops} Stops`;
                const stopsClass = flight.stops === 0 ? 'non-stop' : 
                                 flight.stops === 1 ? 'one-stop' : 
                                 'multiple-stops';

                html += `
                    <div class="result-card">
                        <div class="result-header">
                            <div class="airline">
                                <div class="airline-logo">
                                    <i class="fas fa-plane"></i>
                                </div>
                                <div class="airline-name">${flight.airline}</div>
                            </div>
                            <div class="flight-number">${flight.flight_number}</div>
                        </div>
                        <div class="result-details">
                            <div class="journey-times">
                                <div class="departure">
                                    <div class="time">${flight.departure_time}</div>
                                    <div class="city">${flight.from_city}</div>
                                </div>
                                <div class="journey-duration">
                                    <div class="duration-line"></div>
                                    <div class="stops-indicator">
                                        <span class="stops-label ${stopsClass}">${stopsLabel}</span>
                                    </div>
                                    <div class="duration-time">${flight.duration}</div>
                                    <div class="duration-line"></div>
                                </div>
                                <div class="arrival">
                                    <div class="time">${flight.arrival_time}</div>
                                    <div class="city">${flight.to_city}</div>
                                </div>
                            </div>
                            <div class="journey-date">
                                <div class="date-label">Date</div>
                                <div class="date-value">${formatDate(flight.date)}</div>
                            </div>
                            <div class="price-availability">
                                <div class="price">₹${price}</div>
                                <div class="seats-available">${flight.available_seats} seats left</div>
                            </div>
                            <div class="booking-action">
                                <button class="book-now-btn" data-flight="${flight.flight_number}" data-direction="outbound" data-class="${selectedClass}">Book Now</button>
                            </div>
                        </div>
                        <div class="result-footer">
                            <button class="show-details-btn" data-toggle="flight-details-${flight.flight_number}">
                                <span class="show-text">Show Details</span>
                                <span class="hide-text">Hide Details</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="additional-details" id="flight-details-${flight.flight_number}">
                                <div class="details-section">
                                    <h4>Price Options</h4>
                                    <div class="class-options">
                                        ${Object.entries(flight.prices).map(([cls, price]) => `
                                            <div class="class-option ${cls === selectedClass ? 'selected' : ''}">
                                                <div class="class-name">${cls}</div>
                                                <div class="class-price">₹${price}</div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                                <div class="details-section">
                                    <h4>Amenities</h4>
                                    <div class="amenities-list">
                                        <span class="amenity"><i class="fas fa-wifi"></i> WiFi</span>
                                        <span class="amenity"><i class="fas fa-utensils"></i> Meals</span>
                                        <span class="amenity"><i class="fas fa-tv"></i> Entertainment</span>
                                        <span class="amenity"><i class="fas fa-charging-station"></i> Power Outlets</span>
                                        <span class="amenity"><i class="fas fa-suitcase"></i> Baggage Included</span>
                                        <span class="amenity"><i class="fas fa-couch"></i> Extra Legroom</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += `</div></div>`;
        }

        // Return flights section (for round trips)
        if (returnFlights.length > 0) {
            html += `
                <div class="flight-section return-section">
                    <div class="section-label">Return Flights (${returnFlights.length})</div>
                    <div class="results-list">
            `;

            returnFlights.forEach(flight => {
                const selectedClass = document.getElementById('flight-class').value;
                const price = flight.prices[selectedClass];
                const stopsLabel = flight.stops === 0 ? 'Non-stop' : 
                                  flight.stops === 1 ? '1 Stop' : 
                                  `${flight.stops} Stops`;
                const stopsClass = flight.stops === 0 ? 'non-stop' : 
                                 flight.stops === 1 ? 'one-stop' : 
                                 'multiple-stops';

                html += `
                    <div class="result-card">
                        <div class="result-header">
                            <div class="airline">
                                <div class="airline-logo">
                                    <i class="fas fa-plane fa-flip-horizontal"></i>
                                </div>
                                <div class="airline-name">${flight.airline}</div>
                            </div>
                            <div class="flight-number">${flight.flight_number}</div>
                        </div>
                        <div class="result-details">
                            <div class="journey-times">
                                <div class="departure">
                                    <div class="time">${flight.departure_time}</div>
                                    <div class="city">${flight.from_city}</div>
                                </div>
                                <div class="journey-duration">
                                    <div class="duration-line"></div>
                                    <div class="stops-indicator">
                                        <span class="stops-label ${stopsClass}">${stopsLabel}</span>
                                    </div>
                                    <div class="duration-time">${flight.duration}</div>
                                    <div class="duration-line"></div>
                                </div>
                                <div class="arrival">
                                    <div class="time">${flight.arrival_time}</div>
                                    <div class="city">${flight.to_city}</div>
                                </div>
                            </div>
                            <div class="journey-date">
                                <div class="date-label">Date</div>
                                <div class="date-value">${formatDate(flight.date)}</div>
                            </div>
                            <div class="price-availability">
                                <div class="price">₹${price}</div>
                                <div class="seats-available">${flight.available_seats} seats left</div>
                            </div>
                            <div class="booking-action">
                                <button class="book-now-btn" data-flight="${flight.flight_number}" data-direction="return" data-class="${selectedClass}">Book Now</button>
                            </div>
                        </div>
                        <div class="result-footer">
                            <button class="show-details-btn" data-toggle="flight-details-return-${flight.flight_number}">
                                <span class="show-text">Show Details</span>
                                <span class="hide-text">Hide Details</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="additional-details" id="flight-details-return-${flight.flight_number}">
                                <div class="details-section">
                                    <h4>Price Options</h4>
                                    <div class="class-options">
                                        ${Object.entries(flight.prices).map(([cls, price]) => `
                                            <div class="class-option ${cls === selectedClass ? 'selected' : ''}">
                                                <div class="class-name">${cls}</div>
                                                <div class="class-price">₹${price}</div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                                <div class="details-section">
                                    <h4>Amenities</h4>
                                    <div class="amenities-list">
                                        <span class="amenity"><i class="fas fa-wifi"></i> WiFi</span>
                                        <span class="amenity"><i class="fas fa-utensils"></i> Meals</span>
                                        <span class="amenity"><i class="fas fa-tv"></i> Entertainment</span>
                                        <span class="amenity"><i class="fas fa-charging-station"></i> Power Outlets</span>
                                        <span class="amenity"><i class="fas fa-suitcase"></i> Baggage Included</span>
                                        <span class="amenity"><i class="fas fa-couch"></i> Extra Legroom</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += `</div></div>`;
        }

        container.innerHTML = html;

        // Add event listeners to show/hide details buttons
        const detailButtons = container.querySelectorAll('.show-details-btn');
        detailButtons.forEach(button => {
            button.addEventListener('click', function() {
                const detailsId = this.getAttribute('data-toggle');
                const detailsSection = document.getElementById(detailsId);
                
                if (detailsSection.style.display === 'block') {
                    detailsSection.style.display = 'none';
                    this.classList.remove('active');
                } else {
                    detailsSection.style.display = 'block';
                    this.classList.add('active');
                }
            });
        });

        // Add event listeners to book buttons
        const bookButtons = container.querySelectorAll('.book-now-btn');
        bookButtons.forEach(button => {
            button.addEventListener('click', function() {
                const flightNumber = this.getAttribute('data-flight');
                const direction = this.getAttribute('data-direction');
                const travelClass = this.getAttribute('data-class');
                
                showBookingModal('flight', flightNumber, travelClass, direction);
            });
        });
    }

    // Helper function to show a booking modal
    function showBookingModal(type, id, cls, direction) {
        // Create modal container if it doesn't exist
        let modal = document.querySelector('.booking-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.className = 'booking-modal';
            document.body.appendChild(modal);
        }
        
        let modalContent = '';
        if (type === 'train') {
            modalContent = `
                <div class="booking-modal-content">
                    <div class="booking-modal-header">
                        <h3>Confirm Your Train Booking</h3>
                        <button class="close-modal">&times;</button>
                    </div>
                    <div class="booking-modal-body">
                        <p>You are about to book a ticket for:</p>
                        <div class="booking-details">
                            <p><strong>Train Number:</strong> <span>${id}</span></p>
                            <p><strong>Class:</strong> <span>${getClassFullName(cls)}</span></p>
                            <p><strong>Date:</strong> <span>${document.getElementById('train-date').value}</span></p>
                            <p><strong>Passengers:</strong> <span>${document.getElementById('train-passengers').value}</span></p>
                        </div>
                        <p>This booking feature will be fully implemented in the next phase.</p>
                    </div>
                    <div class="booking-modal-footer">
                        <button class="modal-cancel">Cancel</button>
                        <button class="modal-confirm">Proceed to Booking</button>
                    </div>
                </div>
            `;
        } else {
            modalContent = `
                <div class="booking-modal-content">
                    <div class="booking-modal-header">
                        <h3>Confirm Your Flight Booking</h3>
                        <button class="close-modal">&times;</button>
                    </div>
                    <div class="booking-modal-body">
                        <p>You are about to book a ticket for:</p>
                        <div class="booking-details">
                            <p><strong>Flight Number:</strong> <span>${id}</span></p>
                            <p><strong>Direction:</strong> <span>${direction === 'outbound' ? 'Outbound' : 'Return'}</span></p>
                            <p><strong>Class:</strong> <span>${cls}</span></p>
                            <p><strong>Date:</strong> <span>${direction === 'outbound' ? document.getElementById('flight-depart').value : document.getElementById('flight-return').value}</span></p>
                            <p><strong>Passengers:</strong> <span>${document.getElementById('flight-passengers').value}</span></p>
                        </div>
                        <p>This booking feature will be fully implemented in the next phase.</p>
                    </div>
                    <div class="booking-modal-footer">
                        <button class="modal-cancel">Cancel</button>
                        <button class="modal-confirm">Proceed to Booking</button>
                    </div>
                </div>
            `;
        }
        
        modal.innerHTML = modalContent;
        
        // Show the modal
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
        
        // Add event listeners to buttons
        modal.querySelector('.close-modal').addEventListener('click', () => {
            closeModal(modal);
        });
        
        modal.querySelector('.modal-cancel').addEventListener('click', () => {
            closeModal(modal);
        });
        
        modal.querySelector('.modal-confirm').addEventListener('click', () => {
            alert('Thank you for your booking request! This functionality will be implemented in the next phase.');
            closeModal(modal);
        });
        
        // Close when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
    }
    
    // Helper function to close modal
    function closeModal(modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            if (modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }, 300);
    }

    // Helper function to get full class name from code
    function getClassFullName(classCode) {
        const classNames = {
            'SL': 'Sleeper',
            '3A': 'AC 3 Tier',
            '2A': 'AC 2 Tier',
            '1A': 'AC First Class',
            'CC': 'Chair Car',
            'EC': 'Executive Chair Car'
        };
        
        return classNames[classCode] || classCode;
    }
});

// Add the following CSS for the booking modal
const modalStyles = document.createElement('style');
modalStyles.textContent = `
    .booking-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .booking-modal.show {
        opacity: 1;
    }
    
    .booking-modal-content {
        background-color: var(--card-bg, #fff);
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        width: 90%;
        max-width: 500px;
        animation: modalSlideIn 0.3s ease;
        overflow: hidden;
    }
    
    @keyframes modalSlideIn {
        from { transform: translateY(-30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    .booking-modal-header {
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(to right, rgba(var(--primary-rgb, 0, 123, 255), 0.1), transparent);
    }
    
    .booking-modal-header h3 {
        margin: 0;
        color: var(--primary-color, #0d6efd);
    }
    
    .close-modal {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: var(--text-color-light, #6c757d);
    }
    
    .booking-modal-body {
        padding: 20px;
    }
    
    .booking-details {
        background-color: rgba(var(--bg-rgb, 240, 240, 240), 0.5);
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
    }
    
    .booking-modal-footer {
        padding: 15px 20px;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    
    .modal-cancel {
        padding: 8px 15px;
        border: 1px solid var(--border-color, #dee2e6);
        border-radius: 5px;
        background: none;
        cursor: pointer;
    }
    
    .modal-confirm {
        padding: 8px 15px;
        border: none;
        border-radius: 5px;
        background-color: var(--primary-color, #0d6efd);
        color: white;
        cursor: pointer;
    }
    
    .modal-confirm:hover {
        background-color: var(--primary-color-dark, #0b5ed7);
    }
`;

document.head.appendChild(modalStyles);

// Initialize train search form
function initTrainSearch() {
    const trainForm = document.querySelector('.train-card .booking-card-form');
    if (!trainForm) return;
    
    const searchBtn = trainForm.querySelector('.booking-search-btn');
    
    searchBtn.addEventListener('click', function() {
        const fromStation = trainForm.querySelector('#train-from').value.trim();
        const toStation = trainForm.querySelector('#train-to').value.trim();
        const travelDate = trainForm.querySelector('#train-date').value;
        const travelClass = trainForm.querySelector('#train-class').value;
        const passengers = trainForm.querySelector('#train-passengers').value;
        
        // Validate inputs
        if (!fromStation || !toStation || !travelDate) {
            alert('Please fill in all the required fields for train search.');
            return;
        }
        
        // Redirect to train results page with parameters
        const searchParams = new URLSearchParams({
            from: fromStation,
            to: toStation,
            date: travelDate,
            class: travelClass,
            passengers: passengers
        });
        
        window.location.href = `train_results.php?${searchParams.toString()}`;
    });
}

// Initialize flight search form
function initFlightSearch() {
    const flightForm = document.querySelector('.flight-card .booking-card-form');
    if (!flightForm) return;
    
    const searchBtn = flightForm.querySelector('.booking-search-btn');
    
    searchBtn.addEventListener('click', function() {
        const fromCity = flightForm.querySelector('#flight-from').value.trim();
        const toCity = flightForm.querySelector('#flight-to').value.trim();
        const departDate = flightForm.querySelector('#flight-depart').value;
        const returnDate = flightForm.querySelector('#flight-return').value;
        const passengers = flightForm.querySelector('#flight-passengers').value;
        const flightClass = flightForm.querySelector('#flight-class').value;
        
        // Validate inputs
        if (!fromCity || !toCity || !departDate) {
            alert('Please fill in all the required fields for flight search.');
            return;
        }
        
        // Create search parameters
        const searchParams = new URLSearchParams({
            from: fromCity,
            to: toCity,
            depart: departDate,
            passengers: passengers,
            class: flightClass
        });
        
        // Add return date if provided
        if (returnDate) {
            searchParams.append('return', returnDate);
        }
        
        // Redirect to flight results page with parameters
        window.location.href = `flight_results.php?${searchParams.toString()}`;
    });
}

// Initialize hotel search form
function initHotelSearch() {
    const hotelForm = document.querySelector('.hotel-card .booking-card-form');
    if (!hotelForm) return;
    
    const searchBtn = hotelForm.querySelector('.booking-search-btn');
    
    searchBtn.addEventListener('click', function() {
        const destination = hotelForm.querySelector('#hotel-destination').value.trim();
        const checkIn = hotelForm.querySelector('#hotel-check-in').value;
        const checkOut = hotelForm.querySelector('#hotel-check-out').value;
        const adults = hotelForm.querySelector('#hotel-adults').value;
        const children = hotelForm.querySelector('#hotel-children').value;
        const rooms = hotelForm.querySelector('#hotel-rooms').value;
        
        // Validate inputs
        if (!destination || !checkIn || !checkOut) {
            alert('Please fill in all the required fields for hotel search.');
            return;
        }
        
        // Check that check-out is after check-in
        if (new Date(checkOut) <= new Date(checkIn)) {
            alert('Check-out date must be after check-in date.');
            return;
        }
        
        // Create search parameters
        const searchParams = new URLSearchParams({
            destination: destination,
            checkin: checkIn,
            checkout: checkOut,
            adults: adults,
            children: children,
            rooms: rooms
        });
        
        // Redirect to hotel results page with parameters
        window.location.href = `hotel_results.php?${searchParams.toString()}`;
    });
}

// Helper function to format date in a user-friendly format
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
    return date.toLocaleDateString('en-IN', options);
}

// Function to calculate nights between two dates
function calculateNights(checkIn, checkOut) {
    const start = new Date(checkIn);
    const end = new Date(checkOut);
    const timeDiff = end - start;
    return Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
} 