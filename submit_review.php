<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . APP_URL . "cars.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'You must be logged in to submit a review.'];
    header("Location: " . APP_URL . "login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$car_id = filter_input(INPUT_POST, 'car_id', FILTER_VALIDATE_INT);
$rental_id = filter_input(INPUT_POST, 'rental_id', FILTER_VALIDATE_INT);
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';


$redirect_url = APP_URL . "cars.php"; // وجهة افتراضية إذا كان car_id غير صالح
if ($car_id) {
    $redirect_url = APP_URL . "car_details.php?id=" . $car_id;
}


if (!$car_id) {
    $_SESSION['review_message'] = ['type' => 'danger', 'text' => 'Car ID is missing or invalid.'];
    header("Location: " . $redirect_url);
    exit();
}
if (!$rental_id) {
    $_SESSION['review_message'] = ['type' => 'danger', 'text' => 'Rental ID is missing or invalid for review.'];
    header("Location: " . $redirect_url);
    exit();
}
if ($rating === false) {
    $_SESSION['review_message'] = ['type' => 'danger', 'text' => 'Invalid rating selected. Please choose between 1 and 5 stars.'];
    header("Location: " . $redirect_url);
    exit();
}


try {
    $pdo->beginTransaction();

    $stmt_check_rental = $pdo->prepare("
        SELECT id FROM rentals 
        WHERE id = :rental_id AND user_id = :user_id AND car_id = :car_id AND status = 'completed'
    ");
    $stmt_check_rental->execute([':rental_id' => $rental_id, ':user_id' => $user_id, ':car_id' => $car_id]);
    if (!$stmt_check_rental->fetch()) {
        $pdo->rollBack();
        $_SESSION['review_message'] = ['type' => 'danger', 'text' => 'You can only review completed rentals for this car that you booked. (Ref R1)'];
        header("Location: " . $redirect_url);
        exit();
    }

    $stmt_check_existing = $pdo->prepare("SELECT id FROM reviews WHERE user_id = :user_id AND rental_id = :rental_id");
    $stmt_check_existing->execute([':user_id' => $user_id, ':rental_id' => $rental_id]);
    if ($stmt_check_existing->fetch()) {
        $pdo->rollBack();
        $_SESSION['review_message'] = ['type' => 'warning', 'text' => 'You have already submitted a review for this rental.'];
        header("Location: " . $redirect_url);
        exit();
    }

    $stmt_insert_review = $pdo->prepare("INSERT INTO reviews (user_id, car_id, rental_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
    $stmt_insert_review->execute([$user_id, $car_id, $rental_id, $rating, $comment]);

    $stmt_avg = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(id) as total_reviews FROM reviews WHERE car_id = ?");
    $stmt_avg->execute([$car_id]);
    $avg_data = $stmt_avg->fetch();

    if ($avg_data) {
        $new_avg_rating = $avg_data['avg_rating'] ? round($avg_data['avg_rating'], 2) : null;
        $new_total_reviews = $avg_data['total_reviews'] ?? 0;
        $stmt_update_car_rating = $pdo->prepare("UPDATE cars SET average_rating = ?, total_reviews = ? WHERE id = ?");
        $stmt_update_car_rating->execute([$new_avg_rating, $new_total_reviews, $car_id]);
    }
    
    $pdo->commit();
    $_SESSION['review_message'] = ['type' => 'success', 'text' => 'Thank you! Your review has been submitted.'];

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Submit Review PDOException: " . $e->getMessage());
    $_SESSION['review_message'] = ['type' => 'danger', 'text' => 'An error occurred while submitting your review. Please try again. (Code: SRDB)'];
}

header("Location: " . $redirect_url);
exit();
?>