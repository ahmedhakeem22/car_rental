var map;
// No need for markers array if only showing one
// var markers = []; 

// The pickupLocations array was hardcoded. We will use the injected constants instead.
// const pickupLocations = [
//     { lat: DEFAULT_PICKUP_LAT, lng: DEFAULT_PICKUP_LNG, name: "Main Office - " + DEFAULT_PICKUP_CITY },
// ];


function initMap() {
    const mapElement = document.getElementById("map");
    // Don't initialize if map element isn't on the page (e.g. on car_details page)
    if (!mapElement) {
        // console.log("Map element not found, skipping map initialization.");
        return; 
    }

    // Check if the Google Maps API key is likely set (basic check against placeholder)
    // This assumes you replaced 'YOUR_GOOGLE_MAPS_API_KEY' in config.php
    if (typeof google === 'undefined' || typeof google.maps === 'undefined' || !google.maps.Map || !google.maps.Marker) {
        console.error("Google Maps API not loaded correctly. Check your API key and internet connection.");
        mapElement.innerHTML = "<div class='alert alert-danger text-center'>Failed to load Google Map. Please check the API key configuration.</div>";
        const mapLoadingElement = document.getElementById("map-loading");
        if(mapLoadingElement) mapLoadingElement.style.display = 'none'; // Hide loading
        return;
    }


    // Show loading indicator
    const mapLoadingElement = document.getElementById("map-loading");
    if(mapLoadingElement) mapLoadingElement.style.display = 'block';

    const defaultLocation = { lat: DEFAULT_PICKUP_LAT, lng: DEFAULT_PICKUP_LNG };

    map = new google.maps.Map(mapElement, {
        center: defaultLocation, // Center on the default location
        zoom: 10, // Adjust zoom as needed (e.g., 7-12 depending on desired area view)
    });

    // Add a marker for the default pickup location
    const marker = new google.maps.Marker({
        position: defaultLocation,
        map: map,
        title: "Main Office - " + DEFAULT_PICKUP_CITY
    });
    // markers.push(marker); // Not needed if only one marker

    // Optional: Add an info window
    const infowindow = new google.maps.InfoWindow({
        content: `<h6>Main Office - ${DEFAULT_PICKUP_CITY}</h6><p>Pickup your car here!</p>`
    });
    marker.addListener('click', () => {
        infowindow.open(map, marker);
    });

    // Hide loading after map is likely loaded (a simple timeout or waiting for idle event)
    // Waiting for the 'idle' event is more reliable than a fixed timeout
     google.maps.event.addListenerOnce(map, 'idle', function(){
        if(mapLoadingElement) mapLoadingElement.style.display = 'none'; // Hide loading
     });
}


// Price calculation for booking form (uses jQuery)
$(document).ready(function() {
    const startDateInput = $('#start_date');
    const endDateInput = $('#end_date');
    const totalPriceDisplay = $('#totalPriceDisplay');
    const pricePerDay = parseFloat($('input[name="price_per_day"]').val());

    function calculateTotalPrice() {
        const startDateVal = startDateInput.val();
        const endDateVal = endDateInput.val();

        // Ensure pricePerDay is a valid number
        if (isNaN(pricePerDay)) {
             totalPriceDisplay.text('$0.00'); // Or show an error
             return;
        }


        if (startDateVal && endDateVal) {
            const start = new Date(startDateVal);
            const end = new Date(endDateVal);

            // Clear previous validation styles
            endDateInput.removeClass('is-invalid');
            startDateInput.removeClass('is-invalid');


            if (end < start) {
                totalPriceDisplay.text("End date cannot be before start date.");
                endDateInput.addClass('is-invalid');
                return;
            }

            const today = new Date();
            today.setHours(0,0,0,0); // Normalize today's date

            if (start < today) {
                 totalPriceDisplay.text("Start date cannot be in the past.");
                 startDateInput.addClass('is-invalid');
                 return;
            }


            // Add 1 day because if start and end are same, it's 1 day rental
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;


            if (diffDays > 0) {
                const total = diffDays * pricePerDay;
                totalPriceDisplay.text('$' + total.toFixed(2));
            } else {
                totalPriceDisplay.text('$0.00'); // Should not happen with +1 logic if dates are valid
            }
        } else {
            totalPriceDisplay.text('$0.00');
        }
    }

    // Check if the booking form elements exist on the page before attaching events
    if (startDateInput.length && endDateInput.length && totalPriceDisplay.length && $('input[name="price_per_day"]').length) {
        startDateInput.on('change', calculateTotalPrice);
        endDateInput.on('change', calculateTotalPrice);
        
        // Set min attribute for end_date based on start_date and vice versa
        startDateInput.on('change', function() {
            endDateInput.attr('min', $(this).val());
            if (endDateInput.val() && endDateInput.val() < $(this).val()) {
                endDateInput.val($(this).val()); // Reset end date if it's before new start date
            }
            calculateTotalPrice();
        });

         endDateInput.on('change', function() {
             // Ensure end date is not before start date
             if (startDateInput.val() && $(this).val() < startDateInput.val()) {
                  $(this).val(startDateInput.val());
             }
             calculateTotalPrice();
         });

         // Initial calculation on page load if dates are pre-filled (e.g., after validation error)
         calculateTotalPrice();
    }

    
    // AJAX form submission for booking
    $('#bookingForm').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        const form = $(this);
        const bookingMessageDiv = $('#booking-message');

        // Basic client-side validation (can be enhanced)
        if (!startDateInput.val() || !endDateInput.val()) {
             bookingMessageDiv.html('<div class="alert alert-warning">Please select both start and end dates.</div>');
             return;
        }

        // Clear previous messages
        bookingMessageDiv.html(''); 
        
        // Show loading indicator
        bookingMessageDiv.html('<div class="alert alert-info"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Processing your booking...</div>');

        // Dim the form and disable button
        form.css('opacity', 0.5).find('button[type="submit"]').prop('disabled', true);
        
        // Disable date inputs during processing
        startDateInput.prop('disabled', true);
        endDateInput.prop('disabled', true);


        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize(), // Serialize form data
            dataType: 'json',       // Expect JSON response
            success: function(response) {
                if (response.success) {
                    // Hide the booking message area after the success animation shows
                    bookingMessageDiv.html(''); 
                    showBookingSuccessAnimation(response.rental_id); // Pass rental ID if needed
                    // Optionally update car quantity display on the page if applicable
                    // const quantityElement = $('p:contains("Available Quantity:")');
                    // if (quantityElement.length) {
                    //      let currentQuantity = parseInt(quantityElement.text().replace('Available Quantity:', '').trim());
                    //      if (!isNaN(currentQuantity) && currentQuantity > 0) {
                    //           quantityElement.text('Available Quantity: ' + (currentQuantity - 1));
                    //      }
                    // }

                } else {
                    // Display error message
                    bookingMessageDiv.html('<div class="alert alert-danger">' + response.message + ' <i class="fas fa-times-circle"></i></div>');
                     // Re-enable date inputs on error
                    startDateInput.prop('disabled', false);
                    endDateInput.prop('disabled', false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Handle AJAX errors
                console.error("AJAX Error:", textStatus, errorThrown);
                 console.log("Response text:", jqXHR.responseText);
                bookingMessageDiv.html('<div class="alert alert-danger">An error occurred while processing your booking. Please try again.</div>');
                 // Re-enable date inputs on error
                 startDateInput.prop('disabled', false);
                 endDateInput.prop('disabled', false);
            },
            complete: function() {
                // Re-enable form button regardless of success or error
                form.css('opacity', 1).find('button[type="submit"]').prop('disabled', false);
            }
        });
    });

    // Implement the booking success animation
    function showBookingSuccessAnimation(rentalId = null) {
        // You can replace this with a Bootstrap modal or a more elaborate UI element
        const confirmationSection = `
            <div class="booking-success-animation mt-4">
                <div class="icon"><i class="fas fa-calendar-check"></i></div>
                <h3>Booking Confirmed!</h3>
                <p>Your car rental is successful. Your rental ID is: ${rentalId ? rentalId : 'N/A'}.</p>
                <p>You can view details in your profile.</p>
                <a href="${APP_BASE_URL}profile.php" class="btn btn-outline-primary"><i class="fas fa-user"></i> View My Bookings</a>
                 <button class="btn btn-outline-secondary ms-2" onclick="window.location.reload();"><i class="fas fa-redo"></i> Book Another Car</button>
            </div>
        `;
        // Append it after the form and hide the form
        $('#bookingForm').hide().after(confirmationSection);

        // Optional: Scroll to the new confirmation section
        $('html, body').animate({
            scrollTop: $(".booking-success-animation").offset().top - 100 // Adjust offset as needed
        }, 500);
    }

});

// Ensure initMap is a global function available in the window scope
window.initMap = initMap;