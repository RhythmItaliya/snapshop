<?php
// Razorpay Payment Page
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /snapshop/');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/Env.php';
require_once __DIR__ . '/modal/cart.model.php';
require_once __DIR__ . '/modal/product.model.php';

// Get cart items
$cartItems = [];
$totalAmount = 0;

try {
    $conn = getDatabaseConnection();
    if ($conn) {
        $cartModel = new Cart($conn);
        $cartItems = $cartModel->getCartItems($_SESSION['user_id']);
        
        // Get product details for each cart item
        $productModel = new Product($conn);
        foreach ($cartItems as &$item) {
            $product = $productModel->getProductById($item['product_id']);
            if ($product) {
                $item['product'] = $product;
                $totalAmount += $product['price'] * $item['quantity'];
            }
        }
        $conn->close();
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

if (empty($cartItems)) {
    header('Location: /snapshop/');
    exit;
}

$razorpayConfig = Env::razorpay();
$razorpayKeyId = $razorpayConfig['key_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - SnapShop</title>
    <link href="assets/css/tailwind.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body class="min-h-screen bg-gray-50">
    <?php include 'component/header.php'; ?>

    <div class="pt-20">
        <div class="container mx-auto px-4 py-6">
            <div class="max-w-4xl mx-auto">
                <div class="max-w-md mx-auto">
                    <!-- Payment Section -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Details</h2>
                        
                        <div class="space-y-4">
                            <div class="text-center mb-4">
                                <div class="text-2xl font-bold text-blue-600 mb-2">₹<?php echo number_format($totalAmount, 2); ?></div>
                                <div class="text-gray-600">Total Amount to Pay</div>
                            </div>
                            
                            <button id="payButton" 
                                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                                <i class="fas fa-lock mr-2"></i>
                                Pay ₹<?php echo number_format($totalAmount, 2); ?>
                            </button>

                            <div id="paymentStatus" class="hidden"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'component/footer.php'; ?>
    <?php include 'component/ui/toast.php'; ?>

    <script>
        document.getElementById('payButton').addEventListener('click', async function() {
            const button = this;
            const statusDiv = document.getElementById('paymentStatus');
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            
            try {
                // Create order
                const response = await fetch('/snapshop/api/razorpay/create-order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cart_items: <?php echo json_encode($cartItems); ?>
                    })
                });

                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.message || 'Failed to create order');
                }

                // Initialize Razorpay
                const options = {
                    key: '<?php echo $razorpayKeyId; ?>',
                    amount: data.amount * 100, // Convert to paise
                    currency: 'INR',
                    name: 'SnapShop',
                    description: 'Order Payment',
                    order_id: data.razorpay_order_id,
                    handler: async function(response) {
                        try {
                            // Verify payment
                            const verifyResponse = await fetch('/snapshop/api/razorpay/verify-payment.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    razorpay_payment_id: response.razorpay_payment_id,
                                    razorpay_order_id: response.razorpay_order_id,
                                    razorpay_signature: response.razorpay_signature
                                })
                            });

                            const verifyData = await verifyResponse.json();
                            
                            if (verifyData.success) {
                                if (typeof showToast === 'function') {
                                    showToast('Payment successful! Order confirmed.', 'success', 5000);
                                }
                                
                                // Redirect to success page
                                setTimeout(() => {
                                    window.location.href = '/snapshop/payment-success.php?order_id=' + data.order_id;
                                }, 2000);
                            } else {
                                throw new Error(verifyData.message || 'Payment verification failed');
                            }
                        } catch (error) {
                            console.error('Payment verification error:', error);
                            if (typeof showToast === 'function') {
                                showToast('Payment verification failed: ' + error.message, 'error', 5000);
                            }
                        }
                    },
                    prefill: {
                        name: '<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>',
                        email: '<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>'
                    },
                    theme: {
                        color: '#2563eb'
                    }
                };

                const rzp = new Razorpay(options);
                rzp.open();

            } catch (error) {
                console.error('Payment error:', error);
                if (typeof showToast === 'function') {
                    showToast('Payment failed: ' + error.message, 'error', 5000);
                }
            } finally {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-lock mr-2"></i>Pay ₹<?php echo number_format($totalAmount, 2); ?>';
            }
        });
    </script>
</body>
</html>
