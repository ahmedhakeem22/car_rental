<?php
$page_title = "Booking Confirmation";
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . APP_URL . "login.php");
    exit();
}

if (!isset($_SESSION['booking_success_id'])) {
    header("Location: " . APP_URL . "cars.php");
    exit();
}

$rental_id = $_SESSION['booking_success_id'];
unset($_SESSION['booking_success_id']); 
$rental_details = null;

try {
    $stmt = $pdo->prepare("
        SELECT r.id, r.start_date, r.end_date, r.total_price, r.status, r.payment_status, 
               c.brand, c.model, c.year, c.image,
               u.name as user_name, u.email as user_email
        FROM rentals r
        JOIN cars c ON r.car_id = c.id
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ? AND r.user_id = ?
    ");
    $stmt->execute([$rental_id, $_SESSION['user_id']]);
    $rental_details = $stmt->fetch();

    if (!$rental_details) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Could not retrieve booking details or booking does not belong to you.'];
        header("Location: " . APP_URL . "dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error fetching booking details: ' . $e->getMessage()];
    header("Location: " . APP_URL . "dashboard.php");
    exit();
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0"><i class="bi bi-check-circle-fill me-2"></i>Booking Confirmed!</h4>
                </div>
                <div class="card-body p-4">
                    <p class="lead text-center">Thank you, <?php echo htmlspecialchars($rental_details['user_name']); ?>! Your car rental has been successfully booked.</p>
                    <hr>
                    <h5 class="mb-3">Booking Summary (ID: #<?php echo htmlspecialchars($rental_details['id']); ?>)</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 text-center">
                             <img src="<?php echo APP_URL . 'assets/images/cars/' . (!empty($rental_details['image']) ? htmlspecialchars($rental_details['image']) : 'default-car.png'); ?>" 
                                  alt="<?php echo htmlspecialchars($rental_details['brand'] . ' ' . $rental_details['model']); ?>" 
                                  class="img-fluid rounded" style="max-height: 120px;">
                        </div>
                        <div class="col-md-8">
                            <p><strong>Car:</strong> <?php echo htmlspecialchars($rental_details['brand'] . ' ' . $rental_details['model'] . ' (' . $rental_details['year'] . ')'); ?></p>
                            <p><strong>Rental Period:</strong> <?php echo htmlspecialchars(date('D, M j, Y', strtotime($rental_details['start_date']))); ?> to <?php echo htmlspecialchars(date('D, M j, Y', strtotime($rental_details['end_date']))); ?></p>
                            <p><strong>Total Price:</strong> $<?php echo htmlspecialchars(number_format($rental_details['total_price'], 2)); ?></p>
                        </div>
                    </div>
                    
                    <p><strong>Status:</strong> <span class="badge bg-info text-dark"><?php echo htmlspecialchars(ucfirst($rental_details['status'])); ?></span></p>
                    <p><strong>Payment Status:</strong> <span class="badge bg-warning text-dark"><?php echo htmlspecialchars(ucfirst($rental_details['payment_status'])); ?></span></p>
                    
                    <p class="mt-4 text-muted small">A confirmation email has been sent to <?php echo htmlspecialchars($rental_details['user_email']); ?> (simulation). You can view your booking details on your dashboard.</p>
                    
                    <hr>
                    <div class="text-center mt-4">
                        <a href="<?php echo APP_URL; ?>dashboard.php" class="btn btn-primary mx-2"><i class="bi bi-layout-text-sidebar-reverse me-1"></i>Go to Dashboard</a>
                        <a href="<?php echo APP_URL; ?>cars.php" class="btn btn-outline-secondary mx-2"><i class="bi bi-car-front me-1"></i>Rent Another Car</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>