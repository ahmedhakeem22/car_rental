<?php
$page_title = "Admin Dashboard";
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="page-header">
    <h1><?php echo $page_title; ?></h1>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Total Cars</div>
            <div class="card-body">
                <?php
                    $stmt_cars = $pdo->query("SELECT COUNT(*) FROM cars");
                    $total_cars = $stmt_cars->fetchColumn();
                ?>
                <h5 class="card-title"><?php echo $total_cars; ?></h5>
                <p class="card-text">Registered cars in the system.</p>
                <a href="<?php echo APP_URL; ?>admin/manage_cars.php" class="text-white">View Details »</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Active Rentals</div>
            <div class="card-body">
                 <?php
                    $stmt_rentals = $pdo->query("SELECT COUNT(*) FROM rentals WHERE status = 'booked'");
                    $active_rentals = $stmt_rentals->fetchColumn();
                ?>
                <h5 class="card-title"><?php echo $active_rentals; ?></h5>
                <p class="card-text">Currently active car rentals.</p>
                 <a href="<?php echo APP_URL; ?>admin/manage_rentals.php" class="text-white">View Details »</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info mb-3">
            <div class="card-header">Registered Users</div>
            <div class="card-body">
                <?php
                    $stmt_users = $pdo->query("SELECT COUNT(*) FROM users");
                    $total_users = $stmt_users->fetchColumn();
                ?>
                <h5 class="card-title"><?php echo $total_users; ?></h5>
                <p class="card-text">Total registered client users.</p>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <h4>Quick Actions</h4>
    <p>
        <a href="<?php echo APP_URL; ?>admin/add_car.php" class="btn btn-lg btn-outline-primary me-2">Add New Car</a>
        <a href="<?php echo APP_URL; ?>admin/manage_rentals.php" class="btn btn-lg btn-outline-secondary">View All Rentals</a>
    </p>
</div>


<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>