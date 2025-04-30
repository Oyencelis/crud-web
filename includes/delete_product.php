<?php
require_once 'config.php';

header('Content-Type: application/json');

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['id'])) {
    $id = $data['id'];
    
    // Get product name before deleting
    $stmt = $conn->prepare("SELECT product_name FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $product_name = $product['product_name'] ?? 'Unknown Product';
    $stmt->close();
    
    // Prepare and execute delete query
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Add notification with product name
        $notification_stmt = $conn->prepare("INSERT INTO notifications (type, message) VALUES ('error', ?)");
        $message = "Product deleted: " . $product_name;
        $notification_stmt->bind_param("s", $message);
        $notification_stmt->execute();
        $notification_stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting product: ' . $conn->error
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request or missing product ID'
    ]);
}

$conn->close();
?> 