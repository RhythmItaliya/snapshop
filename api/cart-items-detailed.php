<?php
// Cart Items Detailed API - Fetch user's cart items with complete product details

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
    
    // Get basic cart items
    $cartItems = $cartModel->getCartItems($_SESSION['user_id']);
    $total = $cartModel->getCartTotal($_SESSION['user_id']);
    $totalItems = $cartModel->getCartItemCount($_SESSION['user_id']);
    
    // Enhance cart items with size and color information
    $enhancedCartItems = [];
    foreach ($cartItems as $item) {
        $productId = $item['product_id'];
        
        // Get sizes for this product
        $sizes = [];
        $sizeSql = "SELECT size FROM product_sizes WHERE product_id = ? ORDER BY FIELD(size, 'XS', 'S', 'M', 'L', 'XL', 'XXL')";
        $sizeStmt = $conn->prepare($sizeSql);
        $sizeStmt->bind_param("i", $productId);
        $sizeStmt->execute();
        $sizeResult = $sizeStmt->get_result();
        while ($sizeRow = $sizeResult->fetch_assoc()) {
            $sizes[] = $sizeRow['size'];
        }
        
        // Get colors for this product
        $colors = [];
        $colorSql = "SELECT color FROM product_colors WHERE product_id = ? ORDER BY color";
        $colorStmt = $conn->prepare($colorSql);
        $colorStmt->bind_param("i", $productId);
        $colorStmt->execute();
        $colorResult = $colorStmt->get_result();
        while ($colorRow = $colorResult->fetch_assoc()) {
            $colors[] = $colorRow['color'];
        }
        
        // Create enhanced item with size and color
        $enhancedItem = $item;
        $enhancedItem['size'] = !empty($sizes) ? $sizes[0] : 'N/A';
        $enhancedItem['color'] = !empty($colors) ? $colors[0] : 'N/A';
        $enhancedItem['available_sizes'] = $sizes;
        $enhancedItem['available_colors'] = $colors;
        
        $enhancedCartItems[] = $enhancedItem;
    }
    
    echo json_encode([
        'success' => true,
        'items' => $enhancedCartItems,
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
