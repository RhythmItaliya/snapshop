<?php
// Razorpay Payment Page - Converted from React
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /snapshop/?error=login_required');
    exit;
}

// Check if checkout data exists
if (!isset($_SESSION['checkout_data'])) {
    header('Location: /snapshop/checkout.php');
    exit;
}

// Include required files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/Env.php';
require_once __DIR__ . '/component/ui/button.php';
require_once __DIR__ . '/component/ui/loading-spinner.php';
require_once __DIR__ . '/component/ui/error-state.php';

// Get checkout data from session
$checkoutData = $_SESSION['checkout_data'];
$formData = $checkoutData['formData'];
$cart = $checkoutData['cart'];
$paymentMethod = $checkoutData['paymentMethod'];

// Calculate totals
function calculateSubtotal($cart) {
    if (empty($cart)) return 0;
    return array_reduce($cart, function($total, $item) {
        return $total + ($item['price'] * $item['quantity']);
    }, 0);
}

function calculateTax($subtotal) {
    return $subtotal * 0.18;
}

function calculateTotal($subtotal, $tax) {
    return $subtotal + $tax;
}

$subtotal = calculateSubtotal($cart);
$tax = calculateTax($subtotal);
$total = calculateTotal($subtotal, $tax);

// Get environment variables
$razorpayKey = Env::app('razorpay_key');
$currency = Env::app('currency');
$appName = Env::app('name');
$appDescription = Env::app('description');

// Handle payment success
if (isset($_POST['razorpay_payment_id']) && isset($_POST['razorpay_order_id']) && isset($_POST['razorpay_signature'])) {
    // Payment verification would go here
    // For now, redirect to success page
    $_SESSION['payment_success'] = [
        'orderId' => $_POST['razorpay_order_id'],
        'paymentId' => $_POST['razorpay_payment_id'],
        'amount' => $total,
        'currency' => $currency
    ];
    
    // Clear checkout data
    unset($_SESSION['checkout_data']);
    
    // Redirect to success page
    header('Location: /snapshop/payment-success.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment - SnapShop</title>
    <link href="assets/css/tailwind.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Razorpay Checkout Script -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Include Header -->
    <?php include 'component/header.php'; ?>
    
    <div class="pt-20">
        <div class="container mx-auto px-4 py-16">
            <div class="max-w-2xl mx-auto mt-16">
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h1 class="text-3xl font-bold text-primary mb-6">Complete Your Payment</h1>

                    <div class="bg-gray-100 rounded-xl p-6 mb-8">
                        <h2 class="text-xl font-semibold text-primary mb-4">Order Summary</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between text-neutral">
                                <span>Items (<?php echo count($cart); ?>):</span>
                                <span>₹<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="flex justify-between text-neutral">
                                <span>Tax (18%):</span>
                                <span>₹<?php echo number_format($tax, 2); ?></span>
                            </div>
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between text-lg font-bold text-primary">
                                    <span>Total:</span>
                                    <span>₹<?php echo number_format($total, 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button
                            id="payButton"
                            class="bg-primary text-white px-8 py-4 rounded-lg hover:bg-primary/90 transition-colors text-lg font-semibold w-full disabled:bg-gray-400 disabled:cursor-not-allowed"
                            onclick="handlePayment()"
                        >
                            <span id="payButtonText">Pay ₹<?php echo number_format($total, 2); ?> with Razorpay</span>
                            <span id="payButtonLoading" class="hidden">
                                <div class="flex items-center justify-center space-x-2">
                                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                                    <span>Processing...</span>
                                </div>
                            </span>
                        </button>

                        <p class="text-sm text-neutral mt-4">Secure payment powered by Razorpay</p>
                    </div>
                </div>
            </div>
        </div>
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

        // Payment handling
        function handlePayment() {
            const payButton = document.getElementById('payButton');
            const payButtonText = document.getElementById('payButtonText');
            const payButtonLoading = document.getElementById('payButtonLoading');
            
            // Show loading state
            payButton.disabled = true;
            payButtonText.classList.add('hidden');
            payButtonLoading.classList.remove('hidden');
            
            // Check if Razorpay is loaded
            if (typeof Razorpay === 'undefined') {
                showError('Payment gateway not loaded. Please refresh the page.');
                resetButton();
                return;
            }
            
            // Create order first (you'll need to implement this API endpoint)
            createOrder()
                .then(orderData => {
                    if (!orderData.success) {
                        throw new Error(orderData.message || 'Failed to create order');
                    }
                    
                    // Initialize Razorpay
                    const options = {
                        key: '<?php echo $razorpayKey; ?>',
                        amount: Math.round(<?php echo $total; ?> * 100),
                        currency: '<?php echo $currency; ?>',
                        name: '<?php echo $appName; ?>',
                        description: '<?php echo $appDescription; ?> - Order #' + orderData.order.id,
                        order_id: orderData.order.id,
                        handler: function (response) {
                            handlePaymentSuccess(response, orderData.order.id);
                        },
                        prefill: {
                            name: '<?php echo htmlspecialchars($formData['firstName'] . ' ' . $formData['lastName']); ?>',
                            email: '<?php echo htmlspecialchars($formData['email']); ?>',
                            contact: '<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>',
                        },
                        theme: {
                            color: '#1f2937',
                        },
                        modal: {
                            ondismiss: function () {
                                resetButton();
                            },
                        },
                    };
                    
                    try {
                        const rzp = new Razorpay(options);
                        rzp.open();
                    } catch (rzpError) {
                        throw new Error('Failed to initialize payment gateway');
                    }
                })
                .catch(error => {
                    showError(error.message || 'Payment initialization failed');
                    resetButton();
                });
        }
        
        function createOrder() {
            return fetch('/snapshop/api/razorpay/create-order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    amount: <?php echo $total; ?>,
                    currency: '<?php echo $currency; ?>',
                    receipt: 'receipt_' + Date.now(),
                })
            })
            .then(response => response.json());
        }
        
        function handlePaymentSuccess(response, orderId) {
            // Verify payment with server
            fetch('/snapshop/api/razorpay/verify-payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature,
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Payment verified successfully, redirect to success page
                    window.location.href = '/snapshop/payment-success.php';
                } else {
                    showError(data.message || 'Payment verification failed');
                    resetButton();
                }
            })
            .catch(error => {
                console.error('Payment verification error:', error);
                showError('Payment verification failed. Please contact support.');
                resetButton();
            });
        }
        
        function resetButton() {
            const payButton = document.getElementById('payButton');
            const payButtonText = document.getElementById('payButtonText');
            const payButtonLoading = document.getElementById('payButtonLoading');
            
            payButton.disabled = false;
            payButtonText.classList.remove('hidden');
            payButtonLoading.classList.add('hidden');
        }
        
        function showError(message) {
            if (typeof showToast === 'function') {
                showToast(message, 'error', 5000);
            } else {
                alert(message);
            }
        }
    </script>
</body>
</html>
