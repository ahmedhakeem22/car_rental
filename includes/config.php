<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); 
define('DB_PASS', 'root'); 
define('DB_NAME', 'cars'); 



define('OPENWEATHERMAP_API_KEY', 'bfcd42eac679db58901d2c1223f6a521');
define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY'); 

define('APP_URL', 'http://car_rental.test/'); 
define('SITE_NAME', 'DriveEasy Rentals');

define('DEFAULT_PICKUP_CITY', 'Riyadh'); 
define('DEFAULT_PICKUP_LAT', 24.7136);    
define('DEFAULT_PICKUP_LNG', 46.6753);  

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>