<?php
// Cart Clear API - Clear entire cart for a user

// Start session for user authentication
require_once __DIR__ . '/../auth/auth-helper.php';
startSessionIfNotStarted();

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

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

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
    
    // Clear entire cart
    $result = $cartModel->clearCart($userId);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to clear cart'
        ]);
    }
    
    // Close database connection
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
