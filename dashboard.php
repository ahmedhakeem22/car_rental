<?php
$page_title = "My Dashboard";
require_once __DIR__ . '/includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = "You must be logged in to view your dashboard.";
    header("Location: " . APP_URL . "login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$rentals = [];
$user_name = $_SESSION['user_name'] ?? 'User'; 

try {
    $stmt_rentals = $pdo->prepare("
        SELECT r.id, r.start_date, r.end_date, r.total_price, r.status, r.payment_status, 
               c.brand, c.model, c.image, c.id as car_id
        FROM rentals r
        JOIN cars c ON r.car_id = c.id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt_rentals->execute([$user_id]);
    $rentals = $stmt_rentals->fetchAll();
} catch (PDOException $e) {
    $dashboard_error = "Could not fetch your rentals: " . htmlspecialchars($e->getMessage());
    error_log("Dashboard PDOException: " . $e->getMessage());
}

?>

<div class="container mt-4">
    <div class="page-header mb-4">
        <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
        <p class="lead">This is your personal dashboard where you can manage your rentals.</p>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message']['type'] === 'danger' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['message']['text']); unset($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($dashboard_error)): ?>
        <div class="alert alert-danger"><?php echo $dashboard_error; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Your Rental History</h5>
        </div>
        <div class="card-body">
            <?php if (empty($rentals)): ?>
                <div class="alert alert-info mb-0">
                    You have no rental history yet. 
                    <a href="<?php echo APP_URL; ?>cars.php" class="alert-link">Browse our cars</a> to make a booking!
                </div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($rentals as $rental): ?>
                        <div class="list-group-item list-group-item-action flex-column align-items-start mb-3 border rounded">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-2 text-center">
                                    <a href="<?php echo APP_URL . 'car_details.php?id=' . $rental['car_id']; ?>">
                                        <img src="<?php echo APP_URL . 'assets/images/cars/' . (!empty($rental['image']) ? htmlspecialchars($rental['image']) : 'default-car.png'); ?>"
                                             alt="<?php echo htmlspecialchars($rental['brand'] . ' ' . $rental['model']); ?>"
                                             class="img-fluid rounded" style="max-height: 80px; object-fit: cover;">
                                    </a>
                                </div>
                                <div class="col-md-7">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 fw-bold">
                                            <a href="<?php echo APP_URL . 'car_details.php?id=' . $rental['car_id']; ?>" class="text-decoration-none text-dark">
                                                <?php echo htmlspecialchars($rental['brand'] . ' ' . $rental['model']); ?>
                                            </a>
                                        </h6>
                                        <small class="text-muted">ID: #<?php echo htmlspecialchars($rental['id']); ?></small>
                                    </div>
                                    <p class="mb-1 small">
                                        <strong>Dates:</strong> <?php echo htmlspecialchars(date('M d, Y', strtotime($rental['start_date']))); ?> 
                                        to <?php echo htmlspecialchars(date('M d, Y', strtotime($rental['end_date']))); ?>
                                    </p>
                                    <p class="mb-0 small"><strong>Total Price:</strong> $<?php echo htmlspecialchars(number_format($rental['total_price'], 2)); ?></p>
                                </div>
                                <div class="col-md-3 text-md-end">
                                     <p class="mb-1">
                                        <small>Status:</small><br>
                                        <span class="badge 
                                            <?php 
                                                switch ($rental['status']) {
                                                    case 'booked': echo 'bg-info text-dark'; break;
                                                    case 'completed': echo 'bg-success'; break;
                                                    case 'cancelled': echo 'bg-danger'; break;
                                                    default: echo 'bg-secondary';
                                                }
                                            ?>">
                                            <?php echo htmlspecialchars(ucfirst($rental['status'])); ?>
                                        </span>
                                    </p>
                                    <p class="mb-0">
                                        <small>Payment:</small><br>
                                        <span class="badge
                                            <?php 
                                                switch ($rental['payment_status']) {
                                                    case 'pending': echo 'bg-warning text-dark'; break;
                                                    case 'paid': echo 'bg-success'; break;
                                                    case 'refunded': echo 'bg-primary'; break;
                                                    default: echo 'bg-secondary';
                                                }
                                            ?>">
                                            <?php echo htmlspecialchars(ucfirst($rental['payment_status'])); ?>
                                        </span>
                                    </p>
                                    <?php if ($rental['status'] === 'booked' && $rental['payment_status'] === 'pending'): ?>
                                        <a href="<?php echo APP_URL . 'payment_page.php?rental_id=' . $rental['id']; ?>" class="btn btn-sm btn-warning mt-2">Proceed to Payment</a>
                                    <?php elseif ($rental['status'] === 'booked'): ?>
                                        <!-- <a href="<?php //echo APP_URL . 'cancel_booking.php?rental_id=' . $rental['id']; ?>" class="btn btn-sm btn-outline-danger mt-2" onclick="return confirm('Are you sure you want to cancel this booking?');">Cancel Booking</a> -->
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="<?php echo APP_URL; ?>logout.php" class="btn btn-danger"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php'; 
?>