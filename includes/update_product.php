<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? '';
    $product_name = $_POST['product_name'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    
    // Validate inputs
    if (empty($product_id) || empty($product_name) || empty($category) || $price <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill all required fields'
        ]);
        exit;
    }

    // Determine status based on stock
    $status = 'In Stock';
    if ($stock == 0) {
        $status = 'Out of Stock';
    } elseif ($stock <= 10) {
        $status = 'Low Stock';
    }

    // Prepare and execute query
    $stmt = $conn->prepare("UPDATE products SET product_name = ?, category = ?, price = ?, stock = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssdisi", $product_name, $category, $price, $stock, $status, $product_id);

    if ($stmt->execute()) {
        // Add notification
        $notification_stmt = $conn->prepare("INSERT INTO notifications (type, message) VALUES ('info', ?)");
        $message = "Product updated: " . $product_name;
        $notification_stmt->bind_param("s", $message);
        $notification_stmt->execute();
        $notification_stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Product updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating product: ' . $conn->error
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?> 