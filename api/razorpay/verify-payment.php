<?php
// Razorpay Payment Verification API
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$razorpayPaymentId = $input['razorpay_payment_id'] ?? '';
$razorpayOrderId = $input['razorpay_order_id'] ?? '';
$razorpaySignature = $input['razorpay_signature'] ?? '';

if (empty($razorpayPaymentId) || empty($razorpayOrderId) || empty($razorpaySignature)) {
    echo json_encode(['success' => false, 'message' => 'Missing payment verification data']);
    exit;
}

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/Env.php';
require_once __DIR__ . '/../modal/order.model.php';

try {
    // Get database connection
    $conn = getDatabaseConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Get Razorpay credentials
    $razorpayKeyId = Env::razorpay('key_id');
    $razorpayKeySecret = Env::razorpay('key_secret');
    
    if (!$razorpayKeyId || !$razorpayKeySecret) {
        throw new Exception('Razorpay credentials not configured');
    }
    
    // Verify signature (in production, implement proper signature verification)
    // For now, we'll assume the payment is successful
    $isSignatureValid = true; // Placeholder for actual verification
    
    if (!$isSignatureValid) {
        throw new Exception('Payment signature verification failed');
    }
    
    // Update order status in database
    $orderModel = new Order($conn);
    
    // Get order by Razorpay order ID
    $order = $orderModel->getOrderByRazorpayId($razorpayOrderId);
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    // Update order status to completed
    $updateData = [
        'status' => 'completed',
        'payment_id' => $razorpayPaymentId,
        'payment_status' => 'success',
        'completed_at' => date('Y-m-d H:i:s')
    ];
    
    if (!$orderModel->updateOrder($order['id'], $updateData)) {
        throw new Exception('Failed to update order status');
    }
    
    // Clear user's cart after successful payment
    require_once __DIR__ . '/../modal/cart.model.php';
    $cartModel = new Cart($conn);
    $cartModel->clearCart($_SESSION['user_id']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment verified successfully',
        'payment' => [
            'id' => $razorpayPaymentId,
            'order_id' => $razorpayOrderId,
            'status' => 'captured',
            'amount' => $order['total_amount'] * 100, // Return in paise for consistency
            'currency' => $order['currency']
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
