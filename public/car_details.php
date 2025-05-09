<?php
$page_title = "Car Details";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "Invalid car ID.";
    header("Location: index.php");
    exit();
}
$car_id = $_GET['id'];

// Use prepared statement for fetching car details
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car) {
    $_SESSION['error_message'] = "Car not found.";
    header("Location: index.php");
    exit();
}
// Check availability including quantity
$is_actually_available = $car['is_available'] && $car['quantity'] > 0;
$image_path = !empty($car['image']) ? APP_URL . '../assets/images/' . esc($car['image']) : 'https://via.placeholder.com/300x200.png?text=No+Image';

include __DIR__ . '/partials/header.php';
?>

<div class="container mt-5">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo esc($car['brand'] . ' ' . $car['model']); ?></li>
      </ol>
    </nav>

    <div class="row">
        <div class="col-md-7">
            <img src="<?php echo $image_path; ?>" class="img-fluid rounded shadow-sm mb-3" alt="<?php echo esc($car['brand'] . ' ' . $car['model']); ?>">
            <h2><?php echo esc($car['brand'] . ' ' . $car['model']); ?> <span class="badge bg-secondary"><?php echo esc($car['year']); ?></span></h2>
            <p class="text-muted">Price per day: <span class="fw-bold text-success fs-5">$<?php echo esc(number_format($car['price_per_day'], 2)); ?></span></p>
            <p><strong>Description:</strong></p>
            <p><?php echo nl2br(esc($car['description'])); ?></p>
            <p><strong>Available Quantity:</strong> <?php echo esc($car['quantity']); ?></p>
            <?php if (!$is_actually_available): ?>
                <div class="alert alert-danger" role="alert">
                    This car is currently unavailable for booking.
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3">Book This Car</h4>
                    <?php 
                    // Display weather widget for the booking location
                    displayWeatherWidget(); 
                    ?>

                    <?php if ($is_actually_available): ?>
                        <?php if (isLoggedIn()): ?>
                            <form action="process_booking.php" method="POST" id="bookingForm">
                                <input type="hidden" name="car_id" value="<?php echo esc($car['id']); ?>">
                                <input type="hidden" name="price_per_day" value="<?php echo esc($car['price_per_day']); ?>">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date:</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date:</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required min="<?php echo date('Y-m-d'); ?>">
                                    <div class="invalid-feedback">
                                       End date cannot be before start date.
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <p>Total Price: <span id="totalPriceDisplay" class="fw-bold">$0.00</span></p>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 btn-lg">Book Now</button>
                            </form>
                            <div id="booking-message" class="mt-3">
                                <!-- Booking messages (loading, success, error) will appear here -->
                            </div>
                             <!-- The success animation section will be added here by JS -->
                        <?php else: ?>
                            <div class="alert alert-info">
                                <a href="login.php?redirect=<?php echo urlencode(APP_URL . 'car_details.php?id='.$car_id); ?>">Login</a> or <a href="register.php">Register</a> to book this car.
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                         <!-- Message already displayed above the form -->
                        <p class="text-danger">This car cannot be booked at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>