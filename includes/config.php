<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Replace with your DB username
define('DB_PASS', 'root'); // Replace with your DB password
define('DB_NAME', 'cars'); // Replace with your DB name



// API Keys
define('OPENWEATHERMAP_API_KEY', 'bfcd42eac679db58901d2c1223f6a521');
define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY'); // IMPORTANT: Get your own key!

// Site Settings
define('APP_URL', 'http://car_rental.test/'); // Adjust if needed
define('SITE_NAME', 'DriveEasy Rentals');

// Default Pickup Location (for weather & map - can be made more dynamic)
define('DEFAULT_PICKUP_CITY', 'Riyadh'); // Example City
define('DEFAULT_PICKUP_LAT', 24.7136);    // Example Latitude
define('DEFAULT_PICKUP_LNG', 46.6753);    // Example Longitude

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>