<?php
$page_title = "Our Cars";
require_once __DIR__ . '/includes/header.php';

try {
    $stmt = $pdo->query("
        SELECT c.id, c.model, c.brand, c.year, c.price_per_day, c.image, c.quantity, c.description, c.is_available, 
               COALESCE(c.average_rating, 0) as average_rating, COALESCE(c.total_reviews, 0) as total_reviews
        FROM cars c
        ORDER BY c.brand, c.model
    ");
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
<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['message']['text']); unset($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    <?php if (empty($cars)): ?>
        <div class="col">
            <p>No cars available at the moment. Please check back later.</p>
        </div>
    <?php else: ?>
        <?php foreach ($cars as $car): ?>
            <div class="col">
                <div class="card h-100 shadow-sm car-card-hover <?php echo (!$car['is_available'] || $car['quantity'] <= 0) ? 'border-danger' : ''; ?>">
                    <a href="<?php echo APP_URL . 'car_details.php?id=' . $car['id']; ?>">
                        <img src="<?php echo APP_URL . 'assets/images/cars/' . (!empty($car['image']) ? htmlspecialchars($car['image']) : 'default-car.png'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>" style="height: 200px; object-fit: cover;">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                             <a href="<?php echo APP_URL . 'car_details.php?id=' . $car['id']; ?>" class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>
                            </a>
                        </h5>
                        <p class="card-text text-muted small mb-2"><?php echo htmlspecialchars($car['year']); ?></p>
                        
                        <div class="d-flex align-items-center mb-2 small">
                            <?php if ($car['average_rating'] > 0): ?>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi <?php echo ($i <= floor($car['average_rating'])) ? 'bi-star-fill text-warning' : (($i - 0.5 <= $car['average_rating']) ? 'bi-star-half text-warning' : 'bi-star text-warning'); ?>"></i>
                                <?php endfor; ?>
                                <span class="ms-1 text-muted">(<?php echo round($car['average_rating'],1); ?>)</span>
                            <?php else: ?>
                                <span class="text-muted">No reviews</span>
                            <?php endif; ?>
                        </div>

                        <p class="card-text flex-grow-1 small d-none d-md-block"><?php echo nl2br(htmlspecialchars(substr($car['description'], 0, 70))) . (strlen($car['description']) > 70 ? '...' : ''); ?></p>
                        <p class="card-text h5 mt-auto text-primary">$<?php echo htmlspecialchars(number_format($car['price_per_day'], 2)); ?> <small class="text-muted fs-6">/ day</small></p>
                        
                        <?php if (!$car['is_available'] || $car['quantity'] <= 0): ?>
                            <p class="text-danger fw-bold mt-2 mb-0 small">Out of Stock</p>
                        <?php else: ?>
                            <p class="text-success fw-bold mt-2 mb-0 small">Available (<?php echo htmlspecialchars($car['quantity']); ?> left)</p>
                        <?php endif; ?>
                    </div>
                     <div class="card-footer bg-transparent border-top-0 text-center pb-3">
                         <a href="<?php echo APP_URL . 'car_details.php?id=' . $car['id']; ?>" class="btn btn-sm <?php echo (!$car['is_available'] || $car['quantity'] <= 0) ? 'btn-outline-secondary disabled' : 'btn-primary'; ?> w-100">
                            <?php echo (!$car['is_available'] || $car['quantity'] <= 0) ? 'View Details' : 'Book Now'; ?>
                         </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>