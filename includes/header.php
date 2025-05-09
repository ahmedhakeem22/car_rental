<?php
// File: C:\Users\Zainon\Herd\car_rental\includes\header.php

// config.php includes session_start() if not already started.
require_once __DIR__ . '/config.php';

// We might need $pdo in the header if we display user-specific info,
// but for now, let's assume pages will include it if they need DB for their main content.
// If your navbar itself needs to query the database (e.g., for dynamic menu items based on roles),
// then you would include db_connect.php here.
// require_once __DIR__ . '/db_connect.php';

// Default page title if not set by the including page
if (!isset($page_title)) {
    $page_title = SITE_NAME;
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr"> <?php // Added lang="ar" and dir="rtl" based on your Arabic comments ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/styles.css">
    <?php
    // For page-specific CSS files
    if (isset($page_specific_css) && is_array($page_specific_css)) {
        foreach ($page_specific_css as $css_file) {
            echo '<link rel="stylesheet" href="' . APP_URL . 'assets/css/' . htmlspecialchars($css_file) . '">';
        }
    } elseif (isset($page_specific_css) && is_string($page_specific_css)) {
        echo '<link rel="stylesheet" href="' . APP_URL . 'assets/css/' . htmlspecialchars($page_specific_css) . '">';
    }
    ?>
     <style>
        /* Common error message style, can be moved to styles.css if preferred */
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid red;
            background-color: #ffebee;
        }
    </style>
</head>
<body>
    <div class="container"> <?php // Assuming .container wraps everything including navbar ?>
        <nav class="navbar">
            <div class="logo">
                <a href="<?php echo APP_URL; ?>index.php"> <?php // Link logo to home page ?>
                    <img src="<?php echo APP_URL; ?>assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo">
                </a>
            </div>
            <ul>
                <li><a href="<?php echo APP_URL; ?>cars.php">Cars</a></li> <?php // Example link ?>
                <li><a href="<?php echo APP_URL; ?>discover.php">Discover</a></li> <?php // Example link ?>
                <li><a href="<?php echo APP_URL; ?>gallery.php">Gallery</a></li> <?php // Example link ?>
                <li><a href="#">Templates</a></li>
                <li><a href="#">Updates</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?php echo APP_URL; ?>dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo APP_URL; ?>logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="<?php echo APP_URL; ?>login.php">Login</a></li>
                    <li><a href="<?php echo APP_URL; ?>register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php // Main content of the page will start after this in individual PHP files ?>