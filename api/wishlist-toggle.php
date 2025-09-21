<?php
// Wishlist Toggle Handler - AJAX endpoint for adding/removing products from wishlist

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

if (!$input || !isset($input['product_id']) || !isset($input['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input data'
    ]);
    exit;
}

$productId = intval($input['product_id']);
$action = $input['action'];
$userId = $_SESSION['user_id'];

// Include necessary files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../modal/wishlist.model.php';

try {
    // Get database connection
    $conn = getDatabaseConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Initialize Wishlist model
    $wishlistModel = new Wishlist($conn);
    
    if ($action === 'toggle') {
        // Toggle wishlist status
        $result = $wishlistModel->toggleWishlist($userId, $productId);
        
        if ($result) {
            // Check current status
            $isInWishlist = $wishlistModel->isProductInWishlist($userId, $productId);
            
            echo json_encode([
                'success' => true,
                'in_wishlist' => $isInWishlist,
                'message' => $isInWishlist ? 'Product added to wishlist' : 'Product removed from wishlist'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to toggle wishlist'
            ]);
        }
    } elseif ($action === 'add') {
        // Add to wishlist
        $result = $wishlistModel->addToWishlist($userId, $productId);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'in_wishlist' => true,
                'message' => 'Product added to wishlist'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Product already in wishlist'
            ]);
        }
    } elseif ($action === 'remove') {
        // Remove from wishlist
        $result = $wishlistModel->removeFromWishlist($userId, $productId);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'in_wishlist' => false,
                'message' => 'Product removed from wishlist'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to remove from wishlist'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
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
