<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get unread notifications
    $result = $conn->query("SELECT * FROM notifications WHERE is_read = FALSE ORDER BY created_at DESC");
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mark notifications as read
    $ids = json_decode(file_get_contents('php://input'), true)['ids'] ?? [];
    
    if (!empty($ids)) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmt->execute();
    }
    
    echo json_encode(['success' => true]);
}

$conn->close();
?> 