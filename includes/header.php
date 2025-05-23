<?php
if (session_status() == PHP_SESSION_NONE) { // ابدأ الجلسة هنا إذا لم تبدأ بالفعل
    session_start();
}
require_once __DIR__ . '/config.php'; // يجب أن يكون هذا أولاً بعد session_start (أو داخل config)
require_once __DIR__ . '/db_connect.php';

if (!isset($page_title)) {
    $page_title = SITE_NAME;
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - <?php echo SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link href='https://api.mapbox.com/mapbox-gl-js/v3.2.0/mapbox-gl.css' rel='stylesheet' />
    
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/styles.css">
    
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
<body class="d-flex flex-column min-vh-100"> 
    <div class="main-container flex-grow-1"> 
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top mb-4"> 
            <div class="container-fluid">
                <a class="navbar-brand" href="<?php echo APP_URL; ?>index.php">
                    <img src="<?php echo APP_URL; ?>assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo" style="height: 30px; margin-right: 5px;">
                    <?php echo SITE_NAME; ?>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['cars.php', 'car_details.php'])) ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>cars.php">Cars</a>
                        </li>
                      
                    </ul>
                    <ul class="navbar-nav">
                        <?php if (isset($_SESSION['admin_id'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminNavDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                   <i class="bi bi-person-workspace me-1"></i> Admin Panel
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminNavDropdown">
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>admin/admin_dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>admin/manage_cars.php"><i class="bi bi-car-front-fill me-2"></i>Manage Cars</a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>admin/manage_rentals.php"><i class="bi bi-calendar-check me-2"></i>Manage Rentals</a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>admin/manage_users.php"><i class="bi bi-people-fill me-2"></i>Manage Users</a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>admin/manage_admins.php"><i class="bi bi-person-gear me-2"></i>Manage Admins</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>admin/admin_profile.php"><i class="bi bi-person-circle me-2"></i><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></a></li>
                                    <li><a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>admin/admin_logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                                </ul>
                            </li>
                        <?php elseif (isset($_SESSION['user_id'])): ?>
                             <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" href="#" id="userNavDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                   <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userNavDropdown">
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>dashboard.php"><i class="bi bi-layout-text-sidebar-reverse me-2"></i>My Dashboard</a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>profile.php"><i class="bi bi-person-lines-fill me-2"></i>My Profile</a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>my_bookings.php"><i class="bi bi-journal-bookmark-fill me-2"></i>My Bookings</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>login.php">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'register.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>register.php">Register</a>
                            </li>
                            <li class="nav-item d-none d-lg-block">
                                <span class="navbar-text text-muted mx-2">|</span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo APP_URL; ?>admin/admin_login.php">Admin Login</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <main class="container page-content flex-grow-1 py-3">