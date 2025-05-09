<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php'; // Contains ensureLoggedIn

header('Content-Type: application/json'); // Important for AJAX response

// Start session if not already started (config.php does this, but good practice)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

ensureLoggedIn(); // Make sure user is logged in

$user_id = $_SESSION['user_id']; // Get user ID from session

// Filter and validate inputs
$car_id = filter_input(INPUT_POST, 'car_id', FILTER_VALIDATE_INT);
$start_date_str = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
$end_date_str = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
$price_per_day = filter_input(INPUT_POST, 'price_per_day', FILTER_VALIDATE_FLOAT);

// Validate inputs
if ($car_id === false || $car_id === null || !$start_date_str || !$end_date_str || $price_per_day === false || $price_per_day === null || $price_per_day < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing booking data.']);
    exit();
}

try {
    $start_date = new DateTime($start_date_str);
    $end_date = new DateTime($end_date_str);
    $today = new DateTime('today'); // Normalize today's date for comparison

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format.']);
    exit();
}

// More robust date validation server-side
if ($end_date < $start_date) {
    echo json_encode(['success' => false, 'message' => 'End date cannot be before start date.']);
    exit();
}
// Allow booking for today or future dates
if ($start_date < $today) {
     echo json_encode(['success' => false, 'message' => 'Start date cannot be in the past.']);
    exit();
}


// Calculate number of days (+1 because inclusive)
$days_interval = $end_date->diff($start_date)->days + 1; 
$total_price = $days_interval * $price_per_day;

// --- Start Transaction ---
$pdo->beginTransaction();

try {
    // Check car availability and quantity
    // Use SELECT ... FOR UPDATE to prevent race conditions on quantity
    $stmt = $pdo->prepare("SELECT quantity, is_available FROM cars WHERE id = ? FOR UPDATE"); 
    $stmt->execute([$car_id]);
    $car = $stmt->fetch();

    if (!$car || !$car['is_available'] || $car['quantity'] < 1) {
        $pdo->rollBack(); // Rollback the transaction
        echo json_encode(['success' => false, 'message' => 'Sorry, this car is no longer available for booking.']);
        exit();
    }

    // --- Insert Rental ---
    $sql = "INSERT INTO rentals (user_id, car_id, start_date, end_date, total_price, status, payment_status) 
            VALUES (?, ?, ?, ?, ?, 'booked', 'pending')"; // Default status upon booking
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $car_id, $start_date->format('Y-m-d'), $end_date->format('Y-m-d'), $total_price]);
    $rental_id = $pdo->lastInsertId(); // Get the ID of the new rental

    // --- Decrement Car Quantity ---
    $stmt = $pdo->prepare("UPDATE cars SET quantity = quantity - 1 WHERE id = ?");
    $stmt->execute([$car_id]);
    
    // --- Update is_available if quantity drops to 0 ---
    // Fetch the new quantity after decrementing
    $stmtCheck = $pdo->prepare("SELECT quantity FROM cars WHERE id = ?");
    $stmtCheck->execute([$car_id]);
    $updatedCar = $stmtCheck->fetch();
    
    if ($updatedCar && $updatedCar['quantity'] <= 0) {
        // Set is_available to 0 if quantity is zero or less
        $stmtUpdateAvailability = $pdo->prepare("UPDATE cars SET is_available = 0 WHERE id = ?");
        $stmtUpdateAvailability->execute([$car_id]);
    }

    // --- Commit Transaction ---
    $pdo->commit();

    // Set success message in session (for potential redirect after success)
    // $_SESSION['success_message'] = 'Booking successful! Your rental ID is ' . $rental_id . '.'; // Client-side JS handles display now

    // Return success response as JSON
    echo json_encode([
        'success' => true, 
        'message' => 'Booking confirmed! Your journey awaits.',
        'rental_id' => $rental_id // Return rental ID to the client
    ]);
    exit();

} catch (PDOException $e) {
    $pdo->rollBack(); // Rollback on any PDO exception
    // Log error details (recommended in production)
    error_log("PDO Booking error: " . $e->getMessage()); 
    echo json_encode(['success' => false, 'message' => 'Database error during booking. Please try again later.']);
    exit();
} catch (Exception $e) {
    $pdo->rollBack(); // Rollback on any other exception
    // Log error details
    error_log("General Booking error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred during booking. Please try again.']);
    exit();
}
?>