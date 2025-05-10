<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['review_id'])) {
    echo json_encode(['success' => false, 'vote_type' => null]);
    exit();
}

$user_id = $_SESSION['user_id'];
$review_id = filter_var($_GET['review_id'], FILTER_VALIDATE_INT);

if (!$review_id) {
    echo json_encode(['success' => false, 'vote_type' => null]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT vote_type FROM review_votes WHERE review_id = ? AND user_id = ?");
    $stmt->execute([$review_id, $user_id]);
    $vote_type = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'vote_type' => $vote_type ?: null]);

} catch (PDOException $e) {
    error_log("Get User Vote Status PDOException: " . $e->getMessage());
    echo json_encode(['success' => false, 'vote_type' => null, 'message' => 'Database error.']);
}
?>