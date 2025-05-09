<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . APP_URL . "cars.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = "You must be logged in to make a booking.";
    header("Location: " . APP_URL . "login.php?redirect=" . urlencode(APP_URL . 'car_details.php?id=' . $_POST['car_id']));
    exit();
}

$user_id = $_SESSION['user_id'];
$car_id = filter_input(INPUT_POST, 'car_id', FILTER_VALIDATE_INT);
$start_date_str = trim($_POST['start_date']);
$end_date_str = trim($_POST['end_date']);
$price_per_day = filter_input(INPUT_POST, 'price_per_day', FILTER_VALIDATE_FLOAT);

if (!$car_id || !$start_date_str || !$end_date_str || !$price_per_day) {
    $_SESSION['booking_error'] = "Invalid booking data provided.";
    header("Location: " . APP_URL . "car_details.php?id=" . $car_id);
    exit();
}

$start_date = new DateTime($start_date_str);
$end_date = new DateTime($end_date_str);

if ($end_date <= $start_date) {
    $_SESSION['booking_error'] = "End date must be after the start date.";
    header("Location: " . APP_URL . "car_details.php?id=" . $car_id);
    exit();
}

$today = new DateTime(date('Y-m-d'));
if ($start_date < $today) {
     $_SESSION['booking_error'] = "Start date cannot be in the past.";
    header("Location: " . APP_URL . "car_details.php?id=" . $car_id);
    exit();
}


$interval = $start_date->diff($end_date);
$total_days = $interval->days;

if ($total_days <= 0) {
     $_SESSION['booking_error'] = "Rental period must be at least 1 day.";
    header("Location: " . APP_URL . "car_details.php?id=" . $car_id);
    exit();
}

$total_price = $total_days * $price_per_day;

try {
    $pdo->beginTransaction();

    $stmt_car = $pdo->prepare("SELECT quantity, is_available FROM cars WHERE id = ? FOR UPDATE");
    $stmt_car->execute([$car_id]);
    $car = $stmt_car->fetch();

    if (!$car || !$car['is_available'] || $car['quantity'] <= 0) {
        $pdo->rollBack();
        $_SESSION['booking_error'] = "Sorry, this car is no longer available for the selected dates or is out of stock.";
        header("Location: " . APP_URL . "car_details.php?id=" . $car_id);
        exit();
    }

    $new_quantity = $car['quantity'] - 1;
    $new_is_available = ($new_quantity > 0) ? 1 : 0;

    $stmt_update_car = $pdo->prepare("UPDATE cars SET quantity = ?, is_available = ? WHERE id = ?");
    $stmt_update_car->execute([$new_quantity, $new_is_available, $car_id]);

    $stmt_insert_rental = $pdo->prepare("INSERT INTO rentals (user_id, car_id, start_date, end_date, total_price, status, payment_status) VALUES (?, ?, ?, ?, ?, 'booked', 'pending')");
    $stmt_insert_rental->execute([$user_id, $car_id, $start_date_str, $end_date_str, $total_price]);
    $rental_id = $pdo->lastInsertId();

    $pdo->commit();

    $_SESSION['booking_success_id'] = $rental_id;
    header("Location: " . APP_URL . "booking_confirmation.php");
    exit();

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Booking PDOException: " . $e->getMessage());
    $_SESSION['booking_error'] = "An error occurred while processing your booking. Please try again. " . $e->getMessage();
    header("Location: " . APP_URL . "car_details.php?id=" . $car_id);
    exit();
}
?>