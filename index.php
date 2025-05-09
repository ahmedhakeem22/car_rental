<?php
require_once __DIR__ . '/includes/header.php'; 

$page_title = "Welcome to " . SITE_NAME; 

$featured_cars = [];
try {
    $stmt_featured = $pdo->query("SELECT id, model, brand, year, price_per_day, image FROM cars WHERE is_available = 1 AND quantity > 0 ORDER BY RAND() LIMIT 3");
    $featured_cars = $stmt_featured->fetchAll();
} catch (PDOException $e) {
    error_log("Index page featured cars PDOException: " . $e->getMessage());
}
?>

<!-- قسم الهيرو (Hero Section) -->
<header class="hero-section text-center text-white d-flex align-items-center justify-content-center">
    <div class="container">
        <h1 class="display-3 fw-bold"><?php echo SITE_NAME; ?></h1>
        <p class="lead col-lg-8 mx-auto">Your journey begins here. Rent the perfect car for your next adventure with ease and confidence.</p>
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
            <a href="<?php echo APP_URL; ?>cars.php" class="btn btn-primary btn-lg px-4 gap-3">Browse Cars</a>
            <a href="#how-it-works" class="btn btn-outline-light btn-lg px-4">Learn More</a>
        </div>
    </div>
</header>

<!-- قسم "كيف يعمل" (How It Works) -->
<section id="how-it-works" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold">How It Works</h2>
        <div class="row text-center g-4">
            <div class="col-md-4">
                <div class="p-3">
                    <i class="bi bi-search display-4 text-primary mb-3"></i>
                    <h4 class="fw-normal">1. Search Cars</h4>
                    <p class="text-muted">Find the perfect car from our wide selection using filters for brand, model, and price.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3">
                    <i class="bi bi-calendar-check display-4 text-primary mb-3"></i>
                    <h4 class="fw-normal">2. Select Dates & Book</h4>
                    <p class="text-muted">Choose your rental dates, review the details, and complete your booking in a few simple steps.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3">
                    <i class="bi bi-car-front-fill display-4 text-primary mb-3"></i>
                    <h4 class="fw-normal">3. Enjoy Your Ride</h4>
                    <p class="text-muted">Pick up your car and enjoy your trip! We ensure a smooth and hassle-free rental experience.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم السيارات المميزة (Featured Cars) -->
<?php if (!empty($featured_cars)): ?>
<section class="featured-cars-section py-5">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold">Featured Cars</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($featured_cars as $car): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm car-card-hover">
                        <a href="<?php echo APP_URL . 'car_details.php?id=' . $car['id']; ?>" class="text-decoration-none">
                            <img src="<?php echo APP_URL . 'assets/images/cars/' . (!empty($car['image']) ? htmlspecialchars($car['image']) : 'default-car.png'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>" style="height: 200px; object-fit: cover;">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <a href="<?php echo APP_URL . 'car_details.php?id=' . $car['id']; ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted small"><?php echo htmlspecialchars($car['year']); ?></p>
                            <p class="card-text h5 mt-auto text-primary">$<?php echo htmlspecialchars(number_format($car['price_per_day'], 2)); ?> <small class="text-muted fs-6">/ day</small></p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 text-center pb-3">
                             <a href="<?php echo APP_URL . 'car_details.php?id=' . $car['id']; ?>" class="btn btn-outline-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?php echo APP_URL; ?>cars.php" class="btn btn-lg btn-dark">View All Cars</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- قسم لماذا تختارنا (Why Choose Us) -->
<section class="why-choose-us-section py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold">Why Choose <?php echo SITE_NAME; ?>?</h2>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-start p-3">
                    <i class="bi bi-shield-check display-6 text-success me-3"></i>
                    <div>
                        <h5 class="fw-semibold">Reliable & Safe</h5>
                        <p class="text-muted">All our vehicles are regularly maintained and inspected for your safety and comfort.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-start p-3">
                    <i class="bi bi-cash-coin display-6 text-warning me-3"></i>
                    <div>
                        <h5 class="fw-semibold">Affordable Prices</h5>
                        <p class="text-muted">We offer competitive pricing and transparent rates with no hidden fees.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-start p-3">
                    <i class="bi bi-headset display-6 text-info me-3"></i>
                    <div>
                        <h5 class="fw-semibold">Excellent Support</h5>
                        <p class="text-muted">Our friendly customer support team is available 24/7 to assist you with any queries.</p>
                    </div>
                </div>
            </div>
             <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-start p-3">
                    <i class="bi bi-geo-alt-fill display-6 text-danger me-3"></i>
                    <div>
                        <h5 class="fw-semibold">Convenient Locations</h5>
                        <p class="text-muted">Pick up and drop off your rental car at multiple convenient locations.</p>
                    </div>
                </div>
            </div>
             <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-start p-3">
                    <i class="bi bi-journal-check display-6 text-secondary me-3"></i>
                    <div>
                        <h5 class="fw-semibold">Easy Booking Process</h5>
                        <p class="text-muted">Our online booking system is simple, fast, and secure.</p>
                    </div>
                </div>
            </div>
             <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-start p-3">
                    <i class="bi bi-palette-fill display-6 text-purple me-3"></i>
                    <div>
                        <h5 class="fw-semibold">Wide Variety of Cars</h5>
                        <p class="text-muted">Choose from a diverse fleet of cars to suit your needs and preferences.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- قسم دعوة للعمل (Call to Action) -->
<section class="cta-section text-center py-5 bg-primary text-white">
    <div class="container">
        <h2 class="fw-bold">Ready for Your Next Adventure?</h2>
        <p class="lead col-lg-8 mx-auto mb-4">Don't wait! Book your perfect car today and hit the road with <?php echo SITE_NAME; ?>.</p>
        <a href="<?php echo APP_URL; ?>cars.php" class="btn btn-light btn-lg px-5 py-3 fw-bold">Book Your Car Now</a>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>