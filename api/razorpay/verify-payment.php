<?php
// Razorpay Payment Verification API
// Prevent any output before headers
ob_start();

require_once __DIR__ . '/../../auth/auth-helper.php';
startSessionIfNotStarted();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/Env.php';
require_once __DIR__ . '/../../modal/order.model.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['razorpay_payment_id']) || !isset($input['razorpay_order_id']) || !isset($input['razorpay_signature'])) {
        throw new Exception('Payment verification data is required');
    }

    $razorpayPaymentId = $input['razorpay_payment_id'];
    $razorpayOrderId = $input['razorpay_order_id'];
    $razorpaySignature = $input['razorpay_signature'];

    // Get Razorpay credentials
    $razorpayConfig = Env::razorpay();
    $keySecret = $razorpayConfig['key_secret'];

    // Verify signature
    $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $keySecret);
    
    if (!hash_equals($expectedSignature, $razorpaySignature)) {
        throw new Exception('Invalid payment signature');
    }

    $conn = getDatabaseConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Find order by Razorpay order ID
    $orderModel = new Order($conn);
    $order = $orderModel->getOrderByRazorpayId($razorpayOrderId);
    
    if (!$order) {
        throw new Exception('Order not found');
    }

    if ($order['user_id'] != $_SESSION['user_id']) {
        throw new Exception('Order does not belong to user');
    }

    // Update order status to confirmed
    $updateData = [
        'status' => 'confirmed',
        'payment_id' => $razorpayPaymentId
    ];
    
    $success = $orderModel->updateOrder($order['id'], $updateData);
    
    if (!$success) {
        throw new Exception('Failed to update order status');
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'message' => 'Payment verified successfully',
        'order_id' => $order['id'],
        'payment_id' => $razorpayPaymentId
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    error_log("Razorpay verify payment fatal error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error occurred'
    ]);
}

// Flush output buffer to ensure JSON response is sent
ob_end_flush();
?>
