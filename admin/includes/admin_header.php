<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db_connect.php';

if (!isset($page_title)) {
    $page_title = SITE_NAME . " - Admin";
}

$current_script = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['admin_id']) && $current_script !== 'admin_login.php') {
    if ($current_script !== 'admin_setup.php') { // Allow access to admin_setup.php
        $_SESSION['admin_error'] = "Please log in to access the admin area.";
        header("Location: " . APP_URL . "admin/admin_login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/styles.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/admin_styles.css">
    <?php
    if (isset($page_specific_css) && is_array($page_specific_css)) {
        foreach ($page_specific_css as $css_file) {
            echo '<link rel="stylesheet" href="' . APP_URL . 'assets/css/' . htmlspecialchars($css_file) . '">';
        }
    } elseif (isset($page_specific_css) && is_string($page_specific_css)) {
        echo '<link rel="stylesheet" href="' . APP_URL . 'assets/css/' . htmlspecialchars($page_specific_css) . '">';
    }
    ?>
</head>
<body class="admin-body">
    <div class="admin-main-container d-flex flex-column min-vh-100">
        <?php if (isset($_SESSION['admin_id']) || $current_script === 'admin_setup.php'): ?>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?php echo APP_URL; ?>admin/admin_dashboard.php">
                    <img src="<?php echo APP_URL; ?>assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo" style="height: 30px; margin-right: 5px;">
                    <?php echo SITE_NAME; ?> Admin
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbarNav" aria-controls="adminNavbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="adminNavbarNav">
                    <?php if (isset($_SESSION['admin_id'])): ?>
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_script == 'admin_dashboard.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>admin/admin_dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (in_array($current_script, ['manage_cars.php', 'add_car.php', 'edit_car.php'])) ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>admin/manage_cars.php"><i class="bi bi-car-front-fill me-1"></i>Manage Cars</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_script == 'manage_rentals.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>admin/manage_rentals.php"><i class="bi bi-calendar-check me-1"></i>Manage Rentals</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_script == 'manage_users.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>admin/manage_users.php"><i class="bi bi-people-fill me-1"></i>Manage Users</a>
                        </li>
                         <li class="nav-item">
                            <a class="nav-link <?php echo ($current_script == 'manage_admins.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>admin/manage_admins.php"><i class="bi bi-person-gear me-1"></i>Manage Admins</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminUserDropdown">
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>admin/admin_profile.php"><i class="bi bi-person-lines-fill me-2"></i>Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>admin/admin_logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
        <?php endif; ?>
        <div class="container-fluid admin-page-content flex-grow-1 py-3">