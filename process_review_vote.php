<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to vote.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

$review_id = filter_var($input['review_id'] ?? null, FILTER_VALIDATE_INT);
$vote_type = trim($input['vote_type'] ?? '');

if (!$review_id || !in_array($vote_type, ['like', 'dislike'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid review ID or vote type.']);
    exit();
}

try {
    $pdo->beginTransaction();

    $stmt_check_review = $pdo->prepare("SELECT id FROM reviews WHERE id = ?");
    $stmt_check_review->execute([$review_id]);
    if (!$stmt_check_review->fetch()) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Review not found.']);
        exit();
    }
    
    $stmt_existing_vote = $pdo->prepare("SELECT vote_type FROM review_votes WHERE review_id = ? AND user_id = ?");
    $stmt_existing_vote->execute([$review_id, $user_id]);
    $existing_vote = $stmt_existing_vote->fetchColumn();

    if ($existing_vote) {
        if ($existing_vote === $vote_type) {
            $stmt_delete_vote = $pdo->prepare("DELETE FROM review_votes WHERE review_id = ? AND user_id = ?");
            $stmt_delete_vote->execute([$review_id, $user_id]);
        } else {
            $stmt_update_vote = $pdo->prepare("UPDATE review_votes SET vote_type = ?, voted_at = CURRENT_TIMESTAMP WHERE review_id = ? AND user_id = ?");
            $stmt_update_vote->execute([$vote_type, $review_id, $user_id]);
        }
    } else {
        $stmt_insert_vote = $pdo->prepare("INSERT INTO review_votes (review_id, user_id, vote_type) VALUES (?, ?, ?)");
        $stmt_insert_vote->execute([$review_id, $user_id, $vote_type]);
    }

    $stmt_likes = $pdo->prepare("SELECT COUNT(*) FROM review_votes WHERE review_id = ? AND vote_type = 'like'");
    $stmt_likes->execute([$review_id]);
    $likes_count = $stmt_likes->fetchColumn();

    $stmt_dislikes = $pdo->prepare("SELECT COUNT(*) FROM review_votes WHERE review_id = ? AND vote_type = 'dislike'");
    $stmt_dislikes->execute([$review_id]);
    $dislikes_count = $stmt_dislikes->fetchColumn();

    $stmt_update_review_counts = $pdo->prepare("UPDATE reviews SET likes_count = ?, dislikes_count = ? WHERE id = ?");
    $stmt_update_review_counts->execute([$likes_count, $dislikes_count, $review_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Vote processed.',
        'likes_count' => (int)$likes_count,
        'dislikes_count' => (int)$dislikes_count,
        'user_vote' => $existing_vote === $vote_type ? null : $vote_type 
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Process Review Vote PDOException: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error processing vote. ' . $e->getMessage()]);
}
?>