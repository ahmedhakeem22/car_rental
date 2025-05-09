<?php
$page_title = "Car Details";
require_once __DIR__ . '/includes/header.php';

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Invalid car ID.'];
    header("Location: " . APP_URL . "cars.php");
    exit();
}
$car_id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch();

    if (!$car) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Car not found.'];
        header("Location: " . APP_URL . "cars.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error fetching car details: ' . $e->getMessage()];
    header("Location: " . APP_URL . "cars.php");
    exit();
}

$min_date = date('Y-m-d');
$min_end_date = date('Y-m-d', strtotime('+1 day'));

?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>cars.php">Cars</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></li>
        </ol>
    </nav>

    <?php if (isset($_SESSION['booking_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['booking_error']); unset($_SESSION['booking_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-7">
            <img src="<?php echo APP_URL . 'assets/images/cars/' . (!empty($car['image']) ? htmlspecialchars($car['image']) : 'default-car.png'); ?>" class="img-fluid rounded shadow-sm mb-3" alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>">
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="card-title h2 mb-3"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h1>
                    <p class="card-text text-muted">Year: <?php echo htmlspecialchars($car['year']); ?></p>
                    <p class="card-text h4 text-primary mb-3">
                        $<?php echo htmlspecialchars(number_format($car['price_per_day'], 2)); ?> <small class="text-muted">/ day</small>
                    </p>
                    <p class="card-text">
                        <?php if ($car['is_available'] && $car['quantity'] > 0): ?>
                            <span class="badge bg-success">Available (<?php echo htmlspecialchars($car['quantity']); ?> in stock)</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Out of Stock</span>
                        <?php endif; ?>
                    </p>
                    <hr>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($car['description'])); ?></p>
                    
                    <?php if ($car['is_available'] && $car['quantity'] > 0): ?>
                        <hr>
                        <h5 class="mb-3">Rent This Car</h5>
                        <form action="<?php echo APP_URL; ?>process_booking.php" method="POST" id="rentalForm">
                            <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                            <input type="hidden" name="price_per_day" value="<?php echo $car['price_per_day']; ?>">
                            
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" min="<?php echo $min_date; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" min="<?php echo $min_end_date; ?>" required>
                            </div>
                            <div class="mb-3">
                                <p>Total Days: <span id="total_days">0</span></p>
                                <p>Estimated Price: $<span id="estimated_price">0.00</span></p>
                            </div>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button type="submit" class="btn btn-primary w-100 btn-lg">Book Now</button>
                            <?php else: ?>
                                <p class="text-center">
                                    <a href="<?php echo APP_URL; ?>login.php?redirect=<?php echo urlencode(APP_URL . 'car_details.php?id=' . $car['id']); ?>" class="btn btn-warning w-100 btn-lg">Login to Book</a>
                                </p>
                                <p class="text-center mt-2 small text-muted">New user? <a href="<?php echo APP_URL; ?>register.php">Register here</a>.</p>
                            <?php endif; ?>
                        </form>
                    <?php else: ?>
                         <a href="<?php echo APP_URL; ?>cars.php" class="btn btn-secondary w-100 mt-3">Back to Car List</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const totalDaysSpan = document.getElementById('total_days');
    const estimatedPriceSpan = document.getElementById('estimated_price');
    const pricePerDay = parseFloat(<?php echo $car['price_per_day']; ?>);

    function calculateRental() {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        if (startDateInput.value && endDateInput.value && endDate > startDate) {
            const timeDiff = endDate.getTime() - startDate.getTime();
            const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
            
            if (dayDiff > 0) {
                totalDaysSpan.textContent = dayDiff;
                estimatedPriceSpan.textContent = (dayDiff * pricePerDay).toFixed(2);
                 endDateInput.min = startDateInput.value ? new Date(new Date(startDateInput.value).setDate(new Date(startDateInput.value).getDate() + 1)).toISOString().split('T')[0] : '<?php echo $min_end_date; ?>';
            } else {
                totalDaysSpan.textContent = '0';
                estimatedPriceSpan.textContent = '0.00';
            }
        } else {
            totalDaysSpan.textContent = '0';
            estimatedPriceSpan.textContent = '0.00';
            if(startDateInput.value) {
                 endDateInput.min = new Date(new Date(startDateInput.value).setDate(new Date(startDateInput.value).getDate() + 1)).toISOString().split('T')[0];
            }
        }
    }
    
    startDateInput.addEventListener('change', function() {
        if (this.value) {
            let nextDay = new Date(this.value);
            nextDay.setDate(nextDay.getDate() + 1);
            endDateInput.min = nextDay.toISOString().split('T')[0];
            if (endDateInput.value && new Date(endDateInput.value) <= new Date(this.value)) {
                endDateInput.value = ''; 
            }
        } else {
             endDateInput.min = '<?php echo $min_end_date; ?>';
        }
        calculateRental();
    });
    endDateInput.addEventListener('change', calculateRental);
});
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>