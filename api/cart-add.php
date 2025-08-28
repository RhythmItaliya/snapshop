<?php
// Cart Add API - Add products to cart

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
require_once __DIR__ . '/../modal/product.model.php';

try {
    // Get database connection
    $conn = getDatabaseConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Get product details for price
    $productModel = new Product($conn);
    $product = $productModel->getProductById($productId);
    
    if (!$product) {
        throw new Exception("Product not found with ID: " . $productId);
    }
    
    // Initialize Cart model
    $cartModel = new Cart($conn);
    
    // Add product to cart
    $result = $cartModel->addToCart($userId, $productId, $quantity, $product['price']);
    
    if ($result) {
        // Get updated cart data
        $total = $cartModel->getCartTotal($userId);
        $totalItems = $cartModel->getCartItemCount($userId);
        
        echo json_encode([
            'success' => true,
            'total' => $total,
            'total_items' => $totalItems,
            'message' => 'Product added to cart successfully',
            'debug' => [
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $product['price'],
                'user_id' => $userId
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add product to cart'
        ]);
    }
    
    // Close database connection
    $conn->close();
    
} catch (Exception $e) {
    error_log("Cart Add Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => [
            'product_id' => $productId,
            'quantity' => $quantity,
            'user_id' => $userId,
            'error' => $e->getMessage()
        ]
    ]);
}
?>
