<?php
// (This file might grow with more functions as the app develops)

function fetchWeather($city, $apiKey) {
    // Check if API key is set
    if (empty($apiKey) || $apiKey == 'YOUR_OPENWEATHERMAP_API_KEY') { // Check against default placeholder
         error_log("OpenWeatherMap API key not set.");
         return null;
    }
    
    $apiUrl = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid=" . $apiKey . "&units=metric";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    // Removed CURLOPT_SSL_VERIFYPEER, false as it's a security risk. 
    // If you have issues, configure your environment properly or use a cert.
    // For development on localhost, you might temporarily uncomment below, but NEVER on production.
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        error_log("cURL Error: " . curl_error($ch));
        curl_close($ch);
        return null;
    }

    curl_close($ch);

    if ($httpCode == 200) {
        return json_decode($response, true);
    } else {
         error_log("OpenWeatherMap API returned HTTP code: " . $httpCode . " Response: " . $response);
    }
    return null;
}

function displayWeatherWidget() {
    // Check if API key is set before attempting to fetch
    if (empty(OPENWEATHERMAP_API_KEY) || OPENWEATHERMAP_API_KEY == 'YOUR_OPENWEATHERMAP_API_KEY') {
         echo "<div id='weather-widget' class='alert alert-warning'>Weather widget requires an OpenWeatherMap API key.</div>";
         return;
    }

    $weatherData = fetchWeather(DEFAULT_PICKUP_CITY, OPENWEATHERMAP_API_KEY);
    if ($weatherData && isset($weatherData['main'])) {
        $temp = round($weatherData['main']['temp']);
        $description = ucwords($weatherData['weather'][0]['description']);
        $icon = $weatherData['weather'][0]['icon'];
        $iconUrl = "http://openweathermap.org/img/wn/{$icon}@2x.png";

        echo "<div id='weather-widget' class='alert alert-info'>";
        echo "<h5>Weather in " . htmlspecialchars(DEFAULT_PICKUP_CITY) . "</h5>";
        echo "<img src='{$iconUrl}' alt='Weather icon' class='weather-icon'> "; // Added class
        echo "<strong>{$temp}°C</strong>, {$description}";
        echo "</div>";
    } else {
        echo "<div id='weather-widget' class='alert alert-warning'>Could not fetch weather data for " . htmlspecialchars(DEFAULT_PICKUP_CITY) . ".</div>";
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function ensureLoggedIn() {
    if (!isLoggedIn()) {
        $_SESSION['error_message'] = "You must be logged in to access this page.";
        header("Location: " . APP_URL . "login.php");
        exit();
    }
}

// Helper for sanitizing output
function esc($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>