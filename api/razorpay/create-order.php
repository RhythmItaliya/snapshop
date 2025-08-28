<?php
// Razorpay Order Creation API
// Prevent any output before headers
ob_start();

session_start();
header('Content-Type: application/json');

// Disable error display in production
error_reporting(0);
ini_set('display_errors', 0);

// Enable error logging for debugging
ini_set('log_errors', 1);
error_log("Razorpay create order API called");

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
    error_log("Input received: " . json_encode($input));
    
    if (!$input || !isset($input['cart_items']) || empty($input['cart_items'])) {
        throw new Exception('Cart items are required');
    }

    $conn = getDatabaseConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Validate connection is working
    if ($conn->ping() === false) {
        throw new Exception('Database connection is not responding');
    }
    
    error_log("Database connection established successfully");

    try {
        // Check if order tables exist, create if they don't
        $orderModel = new Order($conn);
        error_log("Order model created successfully");
        $orderModel->createTable();
        error_log("Order tables setup completed");
    } catch (Exception $e) {
        throw new Exception('Failed to setup order tables: ' . $e->getMessage());
    }

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
    
    try {
        $orderId = $orderModel->createOrder($orderData);
        if (!$orderId) {
            throw new Exception('Failed to create order');
        }
    } catch (Exception $e) {
        throw new Exception('Failed to create order: ' . $e->getMessage());
    }

    // Add order items
    try {
        foreach ($orderItems as $item) {
            $success = $orderModel->addOrderItem($orderId, $item['product_id'], $item['quantity'], $item['price']);
            if (!$success) {
                throw new Exception('Failed to add order item');
            }
        }
    } catch (Exception $e) {
        throw new Exception('Failed to add order items: ' . $e->getMessage());
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
    try {
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
        
        if (curl_error($ch)) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }
        
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Failed to create Razorpay order. HTTP Code: ' . $httpCode . ', Response: ' . $response);
        }

        $razorpayResponse = json_decode($response, true);
        if (!$razorpayResponse || !isset($razorpayResponse['id'])) {
            throw new Exception('Invalid response from Razorpay: ' . $response);
        }
    } catch (Exception $e) {
        throw new Exception('Razorpay API error: ' . $e->getMessage());
    }

    // Update order with Razorpay order ID
    try {
        $success = $orderModel->updateOrderRazorpayId($orderId, $razorpayResponse['id']);
        if (!$success) {
            throw new Exception('Failed to update order with Razorpay ID');
        }
    } catch (Exception $e) {
        throw new Exception('Failed to update order: ' . $e->getMessage());
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'razorpay_order_id' => $razorpayResponse['id'],
        'amount' => $totalAmount,
        'key_id' => $keyId
    ]);

} catch (Exception $e) {
    // Ensure connection is closed on error
    if (isset($conn) && $conn) {
        $conn->close();
    }
    
    error_log("Razorpay create order error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    // Ensure connection is closed on error
    if (isset($conn) && $conn) {
        $conn->close();
    }
    
    error_log("Razorpay create order fatal error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error occurred: ' . $e->getMessage()
    ]);
}

// Flush output buffer to ensure JSON response is sent
ob_end_flush();
?>
