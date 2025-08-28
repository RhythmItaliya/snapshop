<?php
// Payment Success Page - Converted from React
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /snapshop/?error=login_required');
    exit;
}

// Check if payment success data exists
if (!isset($_SESSION['payment_success'])) {
    header('Location: /snapshop/');
    exit;
}

// Include required files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/component/ui/loading-spinner.php';
require_once __DIR__ . '/component/ui/error-state.php';

// Get payment success data from session
$paymentData = $_SESSION['payment_success'] ?? null;

if (!$paymentData) {
    // If no payment data, redirect to home
    header('Location: /snapshop/');
    exit;
}

$orderId = $paymentData['orderId'] ?? '';
$paymentId = $paymentData['paymentId'] ?? '';
$amount = $paymentData['amount'] ?? 0;
$currency = $paymentData['currency'] ?? 'INR';

// Initialize variables
$paymentStatus = null;
$loading = false;
$error = null;

// Fetch payment status (placeholder - you'll implement the actual API call)
try {
    // This is where you would make an API call to get payment details
    // For now, we'll use the session data
    $paymentStatus = [
        'id' => $paymentId,
        'amount' => $amount * 100, // Razorpay uses amount in paise
        'currency' => $currency,
        'status' => 'captured',
        'method' => 'Razorpay',
        'created_at' => time()
    ];
    
    $loading = false;
} catch (Exception $e) {
    $error = 'Failed to fetch payment details. Please try again.';
    $loading = false;
}

// Clear payment success data after displaying
unset($_SESSION['payment_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - SnapShop</title>
    <link href="assets/css/tailwind.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Lottie Animation -->
    <script src="https://unpkg.com/lottie-web@latest/dist/lottie.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Include Header -->
    <?php include 'component/header.php'; ?>
    
    <div class="pt-20">
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
            <div class="flex flex-col items-center justify-center p-6 min-h-screen">
                <div class="flex flex-col lg:flex-row w-full max-w-6xl bg-white p-8 border border-gray-200 rounded-lg shadow-md">
                    <div class="lg:w-2/5 flex flex-col items-center justify-center mb-6 lg:mb-0">
                        <div class="w-3/5 h-3/5">
                            <!-- Success Animation Container -->
                            <div id="successAnimation" class="w-full h-full"></div>
                        </div>
                        <div class="text-center">
                            <h3 class="md:text-3xl text-xl text-gray-900 font-semibold">Payment Done!</h3>
                            <p class="text-gray-600 my-2">Thank you for completing your secure online payment.</p>
                            <p>Have a great day!</p>
                        </div>
                    </div>
                    
                    <div class="lg:w-3/5 lg:pl-8">
                        <h4 class="text-2xl font-semibold mb-6">Payment Details</h4>
                        
                        <?php if ($paymentStatus): ?>
                            <div class="space-y-4">
                                <?php 
                                $paymentDetails = [
                                    ['label' => 'Payment ID', 'value' => $paymentStatus['id']],
                                    ['label' => 'Amount', 'value' => '₹' . number_format($paymentStatus['amount'] / 100, 2)],
                                    ['label' => 'Currency', 'value' => strtoupper($paymentStatus['currency'])],
                                    ['label' => 'Status', 'value' => ucfirst($paymentStatus['status'])],
                                    ['label' => 'Payment Method', 'value' => $paymentStatus['method'] ?? 'Razorpay'],
                                    ['label' => 'Created At', 'value' => date('F j, Y, g:i a', $paymentStatus['created_at'])],
                                ];
                                
                                foreach ($paymentDetails as $detail): ?>
                                    <div class="flex items-center space-x-4 border-b border-gray-200 pb-3">
                                        <span class="font-semibold w-1/3 text-gray-900"><?php echo $detail['label']; ?></span>
                                        <div class="w-2/3 overflow-x-auto">
                                            <span><?php echo htmlspecialchars($detail['value']); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php 
                                $orderDetails = [
                                    ['label' => 'Order ID', 'value' => $orderId],
                                    ['label' => 'Payment ID', 'value' => $paymentId],
                                    ['label' => 'Amount', 'value' => '₹' . number_format($amount, 2)],
                                    ['label' => 'Currency', 'value' => $currency],
                                    ['label' => 'Status', 'value' => 'Completed'],
                                    ['label' => 'Payment Method', 'value' => 'Razorpay'],
                                ];
                                
                                foreach ($orderDetails as $detail): ?>
                                    <div class="flex items-center space-x-4 border-b border-gray-200 pb-3">
                                        <span class="font-semibold w-1/3 text-gray-900"><?php echo $detail['label']; ?></span>
                                        <div class="w-2/3 overflow-x-auto">
                                            <span><?php echo htmlspecialchars($detail['value']); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="py-6">
                    <a href="/snapshop/" class="px-6 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded transition-colors">
                        GO BACK
                    </a>
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
            
            // Load success animation
            loadSuccessAnimation();
        });
        
        function loadSuccessAnimation() {
            // Load the success animation from the JSON file
            fetch('/snapshop/assets/img/success.json')
                .then(response => response.json())
                .then(animationData => {
                    const container = document.getElementById('successAnimation');
                    if (container && window.lottie) {
                        window.lottie.loadAnimation({
                            container: container,
                            renderer: 'svg',
                            loop: true,
                            autoplay: true,
                            animationData: animationData
                        });
                    }
                })
                .catch(error => {
                    console.log('Animation not found, showing fallback');
                    // Fallback to a simple success icon if animation fails to load
                    const container = document.getElementById('successAnimation');
                    if (container) {
                        container.innerHTML = `
                            <div class="w-full h-full flex items-center justify-center">
                                <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-white text-4xl"></i>
                                </div>
                            </div>
                        `;
                    }
                });
        }
    </script>
</body>
</html>
