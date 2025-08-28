<?php
// Payment Success Page - Converted from React
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /snapshop/?error=login_required');
    exit;
}

// Get order ID from URL parameter or session
$orderId = $_GET['order_id'] ?? $_SESSION['payment_success']['orderId'] ?? '';

if (empty($orderId)) {
    header('Location: /snapshop/');
    exit;
}

// Include required files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/modal/order.model.php';
require_once __DIR__ . '/modal/cart.model.php';
require_once __DIR__ . '/component/ui/loading-spinner.php';
require_once __DIR__ . '/component/ui/error-state.php';

// Initialize variables
$paymentStatus = null;
$loading = true;
$error = null;
$orderDetails = null;

// Fetch order and payment details from database
try {
    $conn = getDatabaseConnection();
    if ($conn) {
        $orderModel = new Order($conn);
        $orderDetails = $orderModel->getOrderById($orderId);
        
        if ($orderDetails && $orderDetails['user_id'] == $_SESSION['user_id']) {
            // Get order items
            $orderItems = $orderModel->getOrderItems($orderId);
            $orderDetails['items'] = $orderItems;
            
            // Set payment status based on order data
            $paymentStatus = [
                'id' => $orderDetails['payment_id'] ?? 'N/A',
                'amount' => $orderDetails['total_amount'] * 100, // Convert to paise for display
                'currency' => 'INR',
                'status' => $orderDetails['status'],
                'method' => ucfirst($orderDetails['payment_method']),
                'created_at' => strtotime($orderDetails['created_at'])
            ];
            
            // Clear the user's cart after successful order
            try {
                $cartModel = new Cart($conn);
                $cartModel->clearCart($_SESSION['user_id']);
                error_log("Cart cleared for user " . $_SESSION['user_id'] . " after order " . $orderId);
            } catch (Exception $e) {
                error_log("Warning: Could not clear cart for user " . $_SESSION['user_id'] . ": " . $e->getMessage());
                // Don't fail the page if cart clearing fails
            }
            
            $loading = false;
        } else {
            $error = 'Order not found or does not belong to you.';
            $loading = false;
        }
        $conn->close();
    } else {
        $error = 'Database connection failed.';
        $loading = false;
    }
} catch (Exception $e) {
    $error = 'Failed to fetch order details. Please try again.';
    $loading = false;
}

// Clear any payment success session data
unset($_SESSION['payment_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - SnapShop</title>
    <link href="assets/css/tailwind.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
    
    <!-- Lottie Animation -->
    <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.6.2/dist/dotlottie-wc.js" type="module"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Include Header -->
    <?php include 'component/header.php'; ?>
    
    <?php if ($loading): ?>
        <!-- Loading State -->
        <div class="container mx-auto px-4 py-16">
            <div class="text-center">
                <?php echo renderLoadingSpinner(['size' => 'lg', 'variant' => 'primary']); ?>
                <p class="mt-4 text-gray-600">Processing your payment...</p>
            </div>
        </div>
    <?php elseif ($error): ?>
        <!-- Error State -->
        <div class="container mx-auto px-4 py-16">
            <?php echo renderErrorState([
                'error' => $error,
                'onRetry' => 'window.location.reload()'
            ]); ?>
        </div>
    <?php else: ?>
        <!-- Success Content -->
        <div class="bg-gray-100 min-h-screen flex flex-col items-center justify-center p-6">
            <div class="w-full max-w-3xl bg-white p-8 border border-gray-200 rounded-lg shadow-md">
                <!-- Animation at the top center -->
                <div class="flex justify-center mb-8">
                    <dotlottie-wc 
                        src="https://lottie.host/28464554-8bdf-4a6c-a649-5d916f95959f/yltjuAJinZ.lottie" 
                        style="width: 250px; height: 250px" 
                        speed="1" 
                        autoplay 
                        loop>
                    </dotlottie-wc>
                </div>
                
                <!-- Order Details Table below -->
                <div class="w-full">
                    <h4 class="text-2xl font-semibold mb-6 text-center">Order & Payment Details</h4>
                        
                    <?php if ($orderDetails && $paymentStatus): ?>
                        <div class="space-y-4">
                            <?php 
                            $orderDetailsDisplay = [
                                ['label' => 'Order ID', 'value' => $orderId],
                                ['label' => 'Order Number', 'value' => $orderDetails['order_number'] ?? 'N/A'],
                                ['label' => 'Payment ID', 'value' => $paymentStatus['id']],
                                ['label' => 'Amount', 'value' => '₹' . number_format($orderDetails['total_amount'], 2)],
                                ['label' => 'Currency', 'value' => strtoupper($paymentStatus['currency'])],
                                ['label' => 'Status', 'value' => ucfirst($paymentStatus['status'])],
                                ['label' => 'Payment Method', 'value' => $paymentStatus['method']],
                                ['label' => 'Order Date', 'value' => date('F j, Y, g:i a', $paymentStatus['created_at'])],
                            ];
                            
                            foreach ($orderDetailsDisplay as $detail): ?>
                                <div class="flex items-center space-x-4 border-b border-gray-200 pb-3">
                                    <span class="font-semibold w-1/3 text-gray-900"><?php echo $detail['label']; ?></span>
                                    <div class="w-2/3 overflow-x-auto">
                                        <span><?php echo htmlspecialchars($detail['value']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (!empty($orderDetails['items'])): ?>
                                <div class="mt-6">
                                    <h5 class="text-lg font-semibold mb-3">Order Items</h5>
                                    <div class="space-y-2">
                                        <?php foreach ($orderDetails['items'] as $item): ?>
                                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                                <span class="font-medium"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                                <span class="text-gray-600">
                                                    Qty: <?php echo $item['quantity']; ?> × ₹<?php echo number_format($item['price'], 2); ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <p class="text-gray-600">Unable to load order details.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Buttons at the bottom -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-4">
                        <a href="/snapshop/" class="px-6 bg-green-600 hover:bg-green-500 text-white font-semibold py-3 rounded transition-colors text-center mt-4">
                            BACK TO HOME
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    </div>

    <!-- Include Footer -->
    <?php include 'component/footer.php'; ?>

    <!-- Include Toast Component -->
    <?php include 'component/ui/toast.php'; ?>

    <!-- Include Auth Modals -->
    <?php include 'auth/login.php'; ?>
    <?php include 'auth/register.php'; ?>

    <!-- Include Cart Sidebar Component -->
    <?php include 'component/ui/cart-sidebar.php'; ?>

    <script>
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            
            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html>
