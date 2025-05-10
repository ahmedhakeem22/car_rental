<?php
// Start output buffering at the very beginning of the script
ob_start();

$page_title = "Manage Cars";
require_once __DIR__ . '/includes/admin_header.php';

$success_message = '';
$error_message = '';

if (isset($_SESSION['car_action_success'])) {
    $success_message = $_SESSION['car_action_success'];
    unset($_SESSION['car_action_success']);
}
if (isset($_SESSION['car_action_error'])) {
    $error_message = $_SESSION['car_action_error'];
    unset($_SESSION['car_action_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_car_id'])) {
    $car_id_to_delete = filter_input(INPUT_POST, 'delete_car_id', FILTER_VALIDATE_INT);
    if ($car_id_to_delete) {
        try {
            $stmt_check_rentals = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE car_id = ? AND status IN ('booked')");
            $stmt_check_rentals->execute([$car_id_to_delete]);
            if ($stmt_check_rentals->fetchColumn() > 0) {
                 $_SESSION['car_action_error'] = "Cannot delete car (ID: {$car_id_to_delete}). It is part of active or pending rentals.";
            } else {
                $stmt_get_image = $pdo->prepare("SELECT image FROM cars WHERE id = ?");
                $stmt_get_image->execute([$car_id_to_delete]);
                $image_to_delete = $stmt_get_image->fetchColumn();

                $stmt_delete = $pdo->prepare("DELETE FROM cars WHERE id = ?");
                if ($stmt_delete->execute([$car_id_to_delete])) {
                    if (!empty($image_to_delete) && file_exists(__DIR__ . '/../assets/images/cars/' . $image_to_delete)) {
                        unlink(__DIR__ . '/../assets/images/cars/' . $image_to_delete);
                    }
                    $_SESSION['car_action_success'] = "Car (ID: {$car_id_to_delete}) deleted successfully.";
                } else {
                    $_SESSION['car_action_error'] = "Error deleting car (ID: {$car_id_to_delete}).";
                }
            }
        } catch (PDOException $e) {
            $_SESSION['car_action_error'] = "Database error deleting car: " . $e->getMessage();
        }
        
        // Use a relative URL to avoid problems
        header("Location: " . APP_URL . "admin/manage_cars.php");
        ob_end_flush(); // End output buffering and send content
        exit();
    }
}

try {
    $stmt = $pdo->query("SELECT id, model, brand, year, price_per_day, quantity, is_available, image FROM cars ORDER BY id DESC");
    $cars = $stmt->fetchAll();
} catch (PDOException $e) {
    $cars = [];
    $error_message = "Could not fetch cars: " . htmlspecialchars($e->getMessage());
}
?>

<div class="page-header">
    <h1><?php echo $page_title; ?></h1>
    <a href="<?php echo APP_URL; ?>admin/add_car.php" class="btn btn-success">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill me-1" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/></svg>
        Add New Car
    </a>
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header">
        Car List
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Year</th>
                        <th>Price/Day</th>
                        <th>Qty</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cars)): ?>
                        <tr>
                            <td colspan="9" class="text-center">No cars found. Add one!</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cars as $car): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($car['id']); ?></td>
                                <td>
                                    <img src="<?php echo APP_URL . 'assets/images/cars/' . (!empty($car['image']) ? htmlspecialchars($car['image']) : 'default-car.png'); ?>" alt="<?php echo htmlspecialchars($car['brand']); ?>" style="width: 60px; height: 40px; object-fit: cover; border-radius: 3px;">
                                </td>
                                <td><?php echo htmlspecialchars($car['brand']); ?></td>
                                <td><?php echo htmlspecialchars($car['model']); ?></td>
                                <td><?php echo htmlspecialchars($car['year']); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($car['price_per_day'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($car['quantity']); ?></td>
                                <td><?php echo $car['is_available'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>'; ?></td>
                                <td>
                                    <a href="<?php echo APP_URL . 'admin/edit_car.php?id=' . $car['id']; ?>" class="btn btn-sm btn-primary me-1" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.813z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>
                                    </a>
                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete car ID <?php echo $car['id']; ?>? This action cannot be undone.');">
                                        <input type="hidden" name="delete_car_id" value="<?php echo $car['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16"><path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
// Make sure to flush output buffer at the end of the script if not already done
if (ob_get_level() > 0) {
    ob_end_flush();
}
?>