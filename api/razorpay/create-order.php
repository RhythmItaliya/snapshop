<?php
// Razorpay Order Creation API
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/Env.php';
require_once __DIR__ . '/../../modal/order.model.php';
require_once __DIR__ . '/../../modal/product.model.php';

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
    
    if (!$input || !isset($input['cart_items']) || empty($input['cart_items'])) {
        throw new Exception('Cart items are required');
    }

    $conn = getDatabaseConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Check if order tables exist, create if they don't
    $orderModel = new Order($conn);
    $orderModel->createTable();

    // Calculate total amount
    $totalAmount = 0;
    $orderItems = [];
    
    foreach ($input['cart_items'] as $item) {
        $productId = (int)$item['product_id'];
        $quantity = (int)$item['quantity'];
        
        // Get product details
        $productModel = new Product($conn);
        $product = $productModel->getProductById($productId);
        
        if (!$product) {
            throw new Exception("Product not found: $productId");
        }
        
        $price = (float)$product['price'];
        $itemTotal = $price * $quantity;
        $totalAmount += $itemTotal;
        
        $orderItems[] = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'price' => $price,
            'product_name' => $product['name']
        ];
    }

    // Create order in database first
    $orderData = [
        'user_id' => $_SESSION['user_id'],
        'total_amount' => $totalAmount,
        'status' => 'placed',
        'payment_method' => 'razorpay'
    ];
    
    $orderId = $orderModel->createOrder($orderData);
    if (!$orderId) {
        throw new Exception('Failed to create order');
    }

    // Add order items
    foreach ($orderItems as $item) {
        $orderModel->addOrderItem($orderId, $item['product_id'], $item['quantity'], $item['price']);
    }

    // Get Razorpay credentials
    $razorpayConfig = Env::razorpay();
    $keyId = $razorpayConfig['key_id'];
    $keySecret = $razorpayConfig['key_secret'];

    if (!$keyId || !$keySecret) {
        throw new Exception('Razorpay credentials not configured');
    }

    // Create Razorpay order
    $razorpayOrderData = [
        'amount' => (int)($totalAmount * 100), // Convert to paise
        'currency' => 'INR',
        'receipt' => 'order_' . $orderId,
        'notes' => [
            'order_id' => $orderId,
            'user_id' => $_SESSION['user_id']
        ]
    ];

    // Make API call to Razorpay
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($razorpayOrderData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($keyId . ':' . $keySecret)
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Failed to create Razorpay order: ' . $response);
    }

    $razorpayResponse = json_decode($response, true);
    
    // Update order with Razorpay order ID
    $orderModel->updateOrderRazorpayId($orderId, $razorpayResponse['id']);

    $conn->close();

    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'razorpay_order_id' => $razorpayResponse['id'],
        'amount' => $totalAmount,
        'key_id' => $keyId
    ]);

} catch (Exception $e) {
    error_log("Razorpay create order error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
