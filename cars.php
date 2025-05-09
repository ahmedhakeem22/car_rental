<?php
$page_title = "Our Cars";
require_once __DIR__ . '/includes/header.php';

try {
    $stmt = $pdo->query("SELECT id, model, brand, year, price_per_day, image, quantity, description, is_available FROM cars ORDER BY brand, model");
    $cars = $stmt->fetchAll();
} catch (PDOException $e) {
    $cars = [];
    echo "<div class='alert alert-danger'>Could not fetch cars: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<h1 class="mb-4"><?php echo $page_title; ?></h1>

<?php if (isset($_SESSION['booking_success'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['booking_success']); unset($_SESSION['booking_success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['booking_error'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['booking_error']); unset($_SESSION['booking_error']); ?></div>
<?php endif; ?>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    <?php if (empty($cars)): ?>
        <div class="col">
            <p>No cars available at the moment. Please check back later.</p>
        </div>
    <?php else: ?>
        <?php foreach ($cars as $car): ?>
            <div class="col">
                <div class="card h-100 <?php echo (!$car['is_available'] || $car['quantity'] <= 0) ? 'border-danger' : ''; ?>">
                    <img src="<?php echo APP_URL . 'assets/images/cars/' . (!empty($car['image']) ? htmlspecialchars($car['image']) : 'default-car.png'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($car['year']); ?></p>
                        <p class="card-text flex-grow-1"><?php echo nl2br(htmlspecialchars(substr($car['description'], 0, 100))) . (strlen($car['description']) > 100 ? '...' : ''); ?></p>
                        <p class="card-text"><strong>Price:</strong> $<?php echo htmlspecialchars(number_format($car['price_per_day'], 2)); ?> / day</p>
                        
                        <?php if (!$car['is_available'] || $car['quantity'] <= 0): ?>
                            <p class="text-danger fw-bold mt-auto">Out of Stock</p>
                            <a href="<?php echo APP_URL . 'car_details.php?id=' . $car['id']; ?>" class="btn btn-secondary mt-2 disabled w-100">Details</a>
                        <?php else: ?>
                            <p class="text-success fw-bold mt-auto">Available (<?php echo htmlspecialchars($car['quantity']); ?> in stock)</p>
                            <a href="<?php echo APP_URL . 'car_details.php?id=' . $car['id']; ?>" class="btn btn-primary mt-2 w-100">View Details & Book</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>