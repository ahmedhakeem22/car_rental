<?php
require_once __DIR__ . '/config.php';
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
<body>
    <div class="main-container">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?php echo APP_URL; ?>index.php">
                    <img src="<?php echo APP_URL; ?>assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo" style="height: 30px;">
                    <?php echo SITE_NAME; ?>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>cars.php">Cars</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Discover</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Gallery</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav">
                        <?php if (isset($_SESSION['admin_id'])): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>admin/admin_dashboard.php">Admin Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>admin/manage_cars.php">Manage Cars</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>admin/admin_logout.php">Admin Logout (<?php echo htmlspecialchars($_SESSION['admin_username']); ?>)</a></li>
                        <?php elseif (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>dashboard.php">My Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>login.php">Login</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>register.php">Register</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo APP_URL; ?>admin/admin_login.php">Admin Login</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container page-content">