<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $rental_id_to_update = filter_input(INPUT_POST, 'rental_id', FILTER_VALIDATE_INT);
    $new_status = isset($_POST['new_status']) ? trim($_POST['new_status']) : null;
    $new_payment_status = isset($_POST['new_payment_status']) ? trim($_POST['new_payment_status']) : null;
    $car_id_of_rental = filter_input(INPUT_POST, 'car_id', FILTER_VALIDATE_INT);

    $allowed_statuses = ['booked', 'completed', 'cancelled'];
    $allowed_payment_statuses = ['pending', 'paid', 'refunded'];

    if ($rental_id_to_update && $car_id_of_rental && 
        $new_status && in_array($new_status, $allowed_statuses) &&
        $new_payment_status && in_array($new_payment_status, $allowed_payment_statuses)) {
        
        try {
            $pdo->beginTransaction();

            $stmt_get_old_status = $pdo->prepare("SELECT status FROM rentals WHERE id = ?");
            $stmt_get_old_status->execute([$rental_id_to_update]);
            $old_status = $stmt_get_old_status->fetchColumn();

            $stmt_update = $pdo->prepare("UPDATE rentals SET status = ?, payment_status = ? WHERE id = ?");
            $stmt_update->execute([$new_status, $new_payment_status, $rental_id_to_update]);

            // Logic for updating car quantity
            // Only adjust quantity if the status transition implies availability change
            if ($old_status !== $new_status) {
                if (($old_status === 'booked' && ($new_status === 'cancelled' || $new_status === 'completed'))) {
                    // Car becomes available
                    $stmt_car_update = $pdo->prepare("UPDATE cars SET quantity = quantity + 1, is_available = 1 WHERE id = ?");
                    $stmt_car_update->execute([$car_id_of_rental]);
                } elseif (($old_status === 'cancelled' || $old_status === 'completed') && $new_status === 'booked') {
                    // Car becomes booked (from a non-booked state)
                    $stmt_decrease_qty = $pdo->prepare("UPDATE cars SET quantity = GREATEST(0, quantity - 1) WHERE id = ?");
                    $stmt_decrease_qty->execute([$car_id_of_rental]);
                    
                    // Update availability based on the new quantity
                    $stmt_update_avail = $pdo->prepare("UPDATE cars SET is_available = IF(quantity > 0, 1, 0) WHERE id = ?");
                    $stmt_update_avail->execute([$car_id_of_rental]);
                }
                // Note: If moving from booked to booked (but changing payment status), or cancelled to cancelled etc. no quantity change.
            }
            
            $pdo->commit();
            $_SESSION['admin_action_success'] = "Rental ID #{$rental_id_to_update} status updated successfully.";

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['admin_action_error'] = "Database error updating rental status: " . $e->getMessage();
        }
        // Redirect after processing
        header("Location: " . APP_URL . "admin/manage_rentals.php");
        exit();
    } else {
        $_SESSION['admin_action_error'] = "Invalid data provided for status update. Please check all fields.";
         // Redirect back even on error
        header("Location: " . APP_URL . "admin/manage_rentals.php");
        exit();
    }
}

// 4. Set page-specific variables (like title) to be used by the header
$page_title = "Manage Rentals";

// 5. Now include the admin header. It will handle its own session checks and start HTML output.
require_once __DIR__ . '/includes/admin_header.php';

// 6. Fetch data for displaying on the page (if not redirected)
try {
    $stmt_rentals = $pdo->query("
        SELECT r.id, r.start_date, r.end_date, r.total_price, r.status, r.payment_status, r.created_at,
               c.brand, c.model, c.id as car_id,
               u.name as user_name, u.email as user_email
        FROM rentals r
        JOIN cars c ON r.car_id = c.id
        JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC
    ");
    $rentals = $stmt_rentals->fetchAll();
} catch (PDOException $e) {
    $rentals = [];
    // Set an error message to be displayed on the page if fetching fails
    $_SESSION['admin_action_error'] = "Could not fetch rentals: " . htmlspecialchars($e->getMessage());
    // $error_message_page can also be used directly if preferred over session for this specific error
}
?>

<div class="page-header">
    <h1><?php echo htmlspecialchars($page_title); ?></h1>
</div>

<?php if (isset($_SESSION['admin_action_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['admin_action_success']); unset($_SESSION['admin_action_success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['admin_action_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['admin_action_error']); unset($_SESSION['admin_action_error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php /* if (isset($error_message_page)): // Alternative way to show DB fetch error
    <div class="alert alert-danger"><?php echo $error_message_page; ?></div>
   endif; */ ?>


<div class="card shadow-sm">
    <div class="card-header">
        All Rental Records
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Car</th>
                        <th>Dates</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Booked On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rentals) && !isset($_SESSION['admin_action_error'])): // Don't show "No records" if there was a fetch error ?>
                        <tr>
                            <td colspan="9" class="text-center">No rental records found.</td>
                        </tr>
                    <?php elseif (!empty($rentals)): ?>
                        <?php foreach ($rentals as $rental): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($rental['id']); ?></td>
                                <td><?php echo htmlspecialchars($rental['user_name']); ?><br><small class="text-muted"><?php echo htmlspecialchars($rental['user_email']); ?></small></td>
                                <td><?php echo htmlspecialchars($rental['brand'] . ' ' . $rental['model']); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($rental['start_date']))); ?> - <?php echo htmlspecialchars(date('M d, Y', strtotime($rental['end_date']))); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($rental['total_price'], 2)); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        $status_class = 'bg-secondary'; // Default
                                        if ($rental['status'] == 'booked') $status_class = 'bg-info text-dark';
                                        elseif ($rental['status'] == 'completed') $status_class = 'bg-success';
                                        elseif ($rental['status'] == 'cancelled') $status_class = 'bg-danger';
                                        echo $status_class;
                                    ?>">
                                        <?php echo htmlspecialchars(ucfirst($rental['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                     <span class="badge <?php 
                                        $payment_status_class = 'bg-secondary'; // Default
                                        if ($rental['payment_status'] == 'pending') $payment_status_class = 'bg-warning text-dark';
                                        elseif ($rental['payment_status'] == 'paid') $payment_status_class = 'bg-success';
                                        elseif ($rental['payment_status'] == 'refunded') $payment_status_class = 'bg-primary'; // Or bg-info text-dark for refunded
                                        echo $payment_status_class;
                                     ?>">
                                        <?php echo htmlspecialchars(ucfirst($rental['payment_status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($rental['created_at']))); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal-<?php echo $rental['id']; ?>">
                                        Update Status
                                    </button>
                                    <!-- Modal for each rental -->
                                    <div class="modal fade" id="updateStatusModal-<?php echo $rental['id']; ?>" tabindex="-1" aria-labelledby="updateStatusModalLabel-<?php echo $rental['id']; ?>" aria-hidden="true">
                                      <div class="modal-dialog">
                                        <div class="modal-content">
                                          <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                            <input type="hidden" name="rental_id" value="<?php echo $rental['id']; ?>">
                                            <input type="hidden" name="car_id" value="<?php echo $rental['car_id']; ?>">
                                            <input type="hidden" name="update_status" value="1">
                                            <div class="modal-header">
                                              <h5 class="modal-title" id="updateStatusModalLabel-<?php echo $rental['id']; ?>">Update Rental #<?php echo htmlspecialchars($rental['id']); ?></h5>
                                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                              <div class="mb-3">
                                                <label for="new_status-<?php echo $rental['id']; ?>" class="form-label">Rental Status</label>
                                                <select class="form-select" id="new_status-<?php echo $rental['id']; ?>" name="new_status" required>
                                                  <option value="booked" <?php echo ($rental['status'] == 'booked' ? 'selected' : ''); ?>>Booked</option>
                                                  <option value="completed" <?php echo ($rental['status'] == 'completed' ? 'selected' : ''); ?>>Completed</option>
                                                  <option value="cancelled" <?php echo ($rental['status'] == 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                                                </select>
                                              </div>
                                              <div class="mb-3">
                                                <label for="new_payment_status-<?php echo $rental['id']; ?>" class="form-label">Payment Status</label>
                                                <select class="form-select" id="new_payment_status-<?php echo $rental['id']; ?>" name="new_payment_status" required>
                                                  <option value="pending" <?php echo ($rental['payment_status'] == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                                  <option value="paid" <?php echo ($rental['payment_status'] == 'paid' ? 'selected' : ''); ?>>Paid</option>
                                                  <option value="refunded" <?php echo ($rental['payment_status'] == 'refunded' ? 'selected' : ''); ?>>Refunded</option>
                                                </select>
                                              </div>
                                            </div>
                                            <div class="modal-footer">
                                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                              <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                          </form>
                                        </div>
                                      </div>
                                    </div>
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
// 7. Include the admin footer
require_once __DIR__ . '/includes/admin_footer.php';
?>