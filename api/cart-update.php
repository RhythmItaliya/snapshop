<?php
// Cart Update API - Update cart item quantities

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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id']) || !isset($input['quantity'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input data'
    ]);
    exit;
}

$productId = intval($input['product_id']);
$quantity = intval($input['quantity']);
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
    
    // Check if item exists in cart
    $existingItem = $cartModel->getCartItem($userId, $productId);
    
    if (!$existingItem) {
        echo json_encode([
            'success' => false,
            'message' => 'Item not found in cart'
        ]);
        exit;
    }
    
    // Update cart item quantity
    $result = $cartModel->updateCartItemQuantity($userId, $productId, $quantity);
    
    if ($result) {
        // Get updated cart data
        $total = $cartModel->getCartTotal($userId);
        $totalItems = $cartModel->getCartItemCount($userId);
        $updatedItem = $cartModel->getCartItem($userId, $productId);
        
        echo json_encode([
            'success' => true,
            'quantity' => $quantity,
            'price' => $updatedItem ? $updatedItem['price'] : 0,
            'total' => $total,
            'total_items' => $totalItems,
            'message' => 'Cart updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update cart'
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
