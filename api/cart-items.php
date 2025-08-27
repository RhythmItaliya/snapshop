<?php
// Cart Items API - Fetch user's cart items

// Start session for user authentication
session_start();

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

// Include necessary files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../modal/cart.model.php';

try {
    // Get database connection
    $conn = getDatabaseConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Initialize Cart model
    $cartModel = new Cart($conn);
    
    // Get cart items
    $cartItems = $cartModel->getCartItems($_SESSION['user_id']);
    $total = $cartModel->getCartTotal($_SESSION['user_id']);
    $totalItems = $cartModel->getCartItemCount($_SESSION['user_id']);
    
    echo json_encode([
        'success' => true,
        'items' => $cartItems,
        'total' => $total,
        'total_items' => $totalItems
    ]);
    
    // Close database connection
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
