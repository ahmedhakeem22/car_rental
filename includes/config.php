<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); 
define('DB_PASS', 'root'); 
define('DB_NAME', 'cars'); 



define('OPENWEATHERMAP_API_KEY', 'bfcd42eac679db58901d2c1223f6a521');
define('MAPBOX_ACCESS_TOKEN', 'pk.eyJ1IjoiYWhtZWQ4OTg5IiwiYSI6ImNtYWljNGxxejBpemIyaXNqcjNkaHkzMDQifQ.NWJStZBxYxubBDDivPZc7Q'); // <-- ضع مفتاحك هنا

define('APP_URL', 'http://car_rental.test/'); 
define('SITE_NAME', 'DriveEasy Rentals');

define('DEFAULT_PICKUP_CITY', 'Riyadh'); 
define('DEFAULT_PICKUP_LAT', 24.7136);    
define('DEFAULT_PICKUP_LNG', 46.6753);  

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>