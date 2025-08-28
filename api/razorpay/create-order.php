<?php
// Razorpay Create Order API
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/Env.php';
require_once __DIR__ . '/../modal/order.model.php';

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$amount = $input['amount'] ?? 0;
$currency = $input['currency'] ?? 'INR';
$receipt = $input['receipt'] ?? '';

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid amount']);
    exit;
}

try {
    // Get database connection
    $conn = getDatabaseConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Create order in database first
    $orderModel = new Order($conn);
    
    $orderData = [
        'user_id' => $_SESSION['user_id'],
        'total_amount' => $amount,
        'currency' => $currency,
        'status' => 'pending',
        'payment_method' => 'razorpay',
        'receipt' => $receipt
    ];
    
    $orderId = $orderModel->createOrder($orderData);
    
    if (!$orderId) {
        throw new Exception('Failed to create order in database');
    }
    
    // Initialize Razorpay
    $razorpayKeyId = Env::razorpay('key_id');
    $razorpayKeySecret = Env::razorpay('key_secret');
    
    if (!$razorpayKeyId || !$razorpayKeySecret) {
        throw new Exception('Razorpay credentials not configured');
    }
    
    // Create Razorpay order
    $razorpayOrderData = [
        'receipt' => $receipt,
        'amount' => round($amount * 100), // Convert to paise
        'currency' => $currency,
        'notes' => [
            'order_id' => $orderId,
            'user_id' => $_SESSION['user_id']
        ]
    ];
    
    // For now, return a mock Razorpay order ID
    // In production, you would make an actual API call to Razorpay
    $razorpayOrderId = 'order_' . $orderId . '_' . time();
    
    // Update order with Razorpay order ID
    $orderModel->updateOrderRazorpayId($orderId, $razorpayOrderId);
    
    echo json_encode([
        'success' => true,
        'order' => [
            'id' => $razorpayOrderId,
            'amount' => $amount,
            'currency' => $currency,
            'receipt' => $receipt
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
