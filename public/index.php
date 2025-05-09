<?php
$page_title = "Find Your Perfect Ride";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/partials/header.php';

// Fetch cars
// Only show cars with quantity > 0 AND is_available = 1 on the index page list
// We will rely on quantity check in car_details for booking logic robustness
$stmt = $pdo->query("SELECT * FROM cars WHERE quantity > 0 AND is_available = 1 ORDER BY brand, model"); 
$cars = $stmt->fetchAll();
?>

<section id="hero" class="py-5 text-center bg-light">
    <div class="container">
        <h1 class="display-4"><?php echo esc(SITE_NAME); ?></h1>
        <p class="lead">Your journey starts here. Rent a car easily and affordably.</p>
        <a href="#cars" class="btn btn-primary btn-lg">Browse Cars</a>
    </div>
</section>

<section id="cars" class="section-padding">
    <div class="container">
        <h2 class="text-center section-title">Our Fleet</h2>
        <?php 
        // Display weather widget - typically near relevant info or site-wide
        displayWeatherWidget(); 
        ?>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (empty($cars)): ?>
                <div class="col-12"> <!-- Make message span full width -->
                   <p class="text-center alert alert-info">No cars available at the moment. Please check back later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($cars as $car): ?>
                    <?php
                        // Check availability including quantity > 0
                        $is_actually_available = $car['is_available'] && $car['quantity'] > 0;
                        // For the index list, we only show cars with quantity > 0 AND is_available = 1
                        // The query above already filters this. So, all cars in $cars should be available for listing.
                        // However, the card styling logic can still check for safety/future changes.
                        // Let's refine this: If the query only gets truly available cars, we don't need the 'unavailable-car' class logic here.
                        // If we wanted to show *all* cars but grey out unavailable ones, the query should be "SELECT * FROM cars"
                        // Sticking to the current query: show only truly available cars. No 'unavailable-car' class needed for these results.
                        
                        $image_path = !empty($car['image']) ? APP_URL . '../assets/images/' . esc($car['image']) : 'https://via.placeholder.com/300x200.png?text=No+Image';
                    ?>
                    <div class="col">
                        <div class="card h-100 car-card"> <!-- Removed $card_class -->
                            <img src="<?php echo $image_path; ?>" class="card-img-top" alt="<?php echo esc($car['brand'] . ' ' . $car['model']); ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo esc($car['brand'] . ' ' . $car['model']); ?></h5>
                                <p class="card-text text-muted"><?php echo esc($car['year']); ?></p>
                                <p class="card-text small"><?php echo nl2br(esc(substr($car['description'], 0, 100))); ?>...</p>
                                <div class="mt-auto">
                                    <p class="price-tag">$<?php echo esc(number_format($car['price_per_day'], 2)); ?> / day</p>
                                     <!-- If the query only returns available cars, the button is always 'View & Book' -->
                                    <a href="car_details.php?id=<?php echo esc($car['id']); ?>" class="btn btn-primary w-100">View & Book</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="locations" class="section-padding bg-light">
    <div class="container">
        <h2 class="text-center section-title">Our Pickup Locations</h2>
        <p class="text-center mb-4">Find our convenient pickup spots on the map below. (Showing default location)</p> <!-- Added clarifying text -->
        <div id="map" class="map-container">
             <!-- Map content will load here -->
        </div>
        <!-- A spinner or loading text for the map -->
        <div id="map-loading" class="text-center">
             <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading map...</span>
            </div>
            <p class="mt-2">Loading map...</p>
        </div>
         <!-- Optional: Add a message if Google Maps API Key is missing -->
        <?php if (GOOGLE_MAPS_API_KEY == 'YOUR_GOOGLE_MAPS_API_KEY'): ?>
             <div class="alert alert-warning text-center mt-3">
                 <strong>Google Maps API Key Missing:</strong> Please replace 'YOUR_GOOGLE_MAPS_API_KEY' in `includes/config.php` with your actual key to display the map.
             </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>