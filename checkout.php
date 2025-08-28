<?php
// Checkout Page - Converted from React
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /snapshop/?error=login_required');
    exit;
}

// Include required files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/modal/cart.model.php';
require_once __DIR__ . '/modal/user.model.php';
require_once __DIR__ . '/component/ui/input.php';
require_once __DIR__ . '/component/ui/button.php';
require_once __DIR__ . '/component/ui/loading-spinner.php';
require_once __DIR__ . '/component/ui/error-state.php';

// Get database connection
$conn = getDatabaseConnection();

// Initialize variables
$user = null;
$cart = [];
$loading = true;
$error = null;

// Get user data
if ($conn) {
    try {
        $userModel = new User($conn);
        $user = $userModel->getUserById($_SESSION['user_id']);
        
        if (!$user) {
            throw new Exception("User not found");
        }
        
        // Ensure address is properly structured
        if (!isset($user['address']) || !is_array($user['address'])) {
            $user['address'] = [
                'street_address' => '',
                'apartment' => '',
                'city' => '',
                'state' => '',
                'country' => '',
                'zip_code' => ''
            ];
        }
        
        // Get cart data with detailed product information
        $cart = [];
        $cartResponse = file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/snapshop/api/cart-items-detailed.php');
        if ($cartResponse !== false) {
            $cartData = json_decode($cartResponse, true);
            if ($cartData && $cartData['success']) {
                $cart = $cartData['items'];
            }
        }
        
        // Fallback to basic cart if detailed API fails
        if (empty($cart)) {
            $cartModel = new Cart($conn);
            $cart = $cartModel->getCartItems($_SESSION['user_id']);
        }
        
        $loading = false;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        $loading = false;
    }
} else {
    $error = "Database connection failed";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_form'])) {
    $formData = [
        'firstName' => trim($_POST['firstName'] ?? ''),
        'lastName' => trim($_POST['lastName'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'state' => trim($_POST['state'] ?? ''),
        'zipCode' => trim($_POST['zipCode'] ?? ''),
        'country' => trim($_POST['country'] ?? ''),
    ];
    
    $paymentMethod = $_POST['paymentMethod'] ?? 'razorpay';
    
    // Validation
    if (empty($formData['firstName']) || empty($formData['lastName']) || empty($formData['email'])) {
        $error = 'Please fill in all required fields';
    } else {
        // Store checkout data in session for payment page
        $_SESSION['checkout_data'] = [
            'formData' => $formData,
            'cart' => $cart,
            'paymentMethod' => $paymentMethod
        ];
        
        // Redirect to payment page
        header('Location: /snapshop/razorpay-payment.php');
        exit;
    }
}

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

// Close database connection
if ($conn) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - SnapShop</title>
    <link href="assets/css/tailwind.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Include Header -->
    <?php include 'component/header.php'; ?>
    
    <div class="pt-20">
        <?php if ($loading): ?>
            <!-- Loading State -->
            <div class="text-center py-20">
                <?php echo renderLoadingSpinner(['size' => 'lg', 'variant' => 'primary']); ?>
                <p class="mt-4 text-gray-600">Loading checkout...</p>
            </div>
        <?php elseif ($error): ?>
            <!-- Error State -->
            <div class="text-center py-20">
                <?php echo renderErrorState([
                    'error' => $error,
                    'onRetry' => 'window.location.reload()'
                ]); ?>
            </div>
        <?php elseif (empty($cart)): ?>
            <!-- Empty Cart State -->
            <div class="text-center py-20">
                <h3 class="text-2xl font-semibold text-primary mb-2">Your Cart is Empty</h3>
                <p class="text-neutral mb-6">Add some products to your cart before checkout.</p>
                <a href="/snapshop/products.php" class="inline-block bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary/90 transition-colors">
                    Browse Products
                </a>
            <?php else: ?>
                            <!-- Debug Information (remove in production) -->
            <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
                <div class="container mx-auto px-4 py-4">
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                        <h4 class="font-bold">Debug Information:</h4>
                        <h5 class="font-semibold mt-2">User Data:</h5>
                        <pre class="text-sm mt-1"><?php echo htmlspecialchars(print_r($user, true)); ?></pre>
                        <h5 class="font-semibold mt-2">Cart Data:</h5>
                        <pre class="text-sm mt-1"><?php echo htmlspecialchars(print_r($cart, true)); ?></pre>
                    </div>
                </div>
            <?php endif; ?>
                
                <!-- Checkout Content -->
            <div class="container mx-auto px-4 py-16">
                <h1 class="text-3xl font-bold text-primary mb-8">Checkout</h1>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Shipping Information Form -->
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-primary mb-6">Shipping Information</h2>
                        
                        <?php 
                        // Check if user has incomplete profile
                        $hasIncompleteProfile = empty($user['first_name']) || empty($user['last_name']) || empty($user['phone']) || 
                                             empty($user['address']['street_address']) || empty($user['address']['city']) || 
                                             empty($user['address']['state']) || empty($user['address']['country']);
                        
                        if ($hasIncompleteProfile): ?>
                            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                                        <div>
                                            <p class="text-blue-800 font-medium">Complete Your Profile</p>
                                            <p class="text-blue-700 text-sm">Some information is missing. Please fill in all required fields to complete your checkout.</p>
                                        </div>
                                    </div>
                                    <a href="/snapshop/profile.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium underline">
                                        Complete Profile
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="checkout_form" value="1">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php echo renderInput([
                                    'label' => 'First Name',
                                    'name' => 'firstName',
                                    'value' => htmlspecialchars($user['first_name'] ?? ''),
                                    'required' => true,
                                    'placeholder' => 'Enter your first name'
                                ]); ?>
                                
                                <?php echo renderInput([
                                    'label' => 'Last Name',
                                    'name' => 'lastName',
                                    'value' => htmlspecialchars($user['last_name'] ?? ''),
                                    'required' => true,
                                    'placeholder' => 'Enter your last name'
                                ]); ?>
                            </div>

                            <?php echo renderInput([
                                'label' => 'Email',
                                'type' => 'email',
                                'name' => 'email',
                                'value' => htmlspecialchars($user['email'] ?? ''),
                                'required' => true
                            ]); ?>

                            <?php echo renderInput([
                                'label' => 'Phone',
                                'type' => 'tel',
                                'name' => 'phone',
                                'value' => htmlspecialchars($user['phone'] ?? ''),
                                'placeholder' => '+91 (555) 123-4567'
                            ]); ?>

                            <?php echo renderInput([
                                'label' => 'Address',
                                'name' => 'address',
                                'value' => htmlspecialchars($user['address']['street_address'] ?? $user['address'] ?? ''),
                                'required' => true
                            ]); ?>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <?php echo renderInput([
                                    'label' => 'City',
                                    'name' => 'city',
                                    'value' => htmlspecialchars($user['address']['city'] ?? ''),
                                    'required' => true
                                ]); ?>
                                
                                <?php echo renderInput([
                                    'label' => 'State',
                                    'name' => 'state',
                                    'value' => htmlspecialchars($user['address']['state'] ?? ''),
                                    'required' => true
                                ]); ?>
                                
                                <?php echo renderInput([
                                    'label' => 'ZIP Code',
                                    'name' => 'zipCode',
                                    'value' => htmlspecialchars($user['address']['zip_code'] ?? ''),
                                    'required' => true
                                ]); ?>
                            </div>

                            <?php echo renderInput([
                                'label' => 'Country',
                                'name' => 'country',
                                'value' => htmlspecialchars($user['address']['country'] ?? ''),
                                'required' => true
                            ]); ?>

                            <div>
                                <label class="block text-sm font-medium text-primary mb-3">Payment Method</label>
                                <div class="space-y-3">
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input
                                            type="radio"
                                            name="paymentMethod"
                                            value="razorpay"
                                            checked
                                            class="text-accent focus:ring-accent focus:ring-2 focus:ring-accent/50"
                                        />
                                        <span class="text-neutral">
                                            Razorpay (Credit/Debit Cards, UPI, Net Banking)
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <?php echo renderButton([
                                'type' => 'submit',
                                'variant' => 'primary',
                                'size' => 'lg',
                                'className' => 'w-full',
                                'children' => 'Proceed to Payment'
                            ]); ?>
                        </form>
                    </div>

                    <!-- Order Summary -->
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-primary mb-6">Order Summary</h2>

                        <div class="space-y-4 mb-6">
                            <?php if (empty($cart)): ?>
                                <div class="text-center py-8">
                                    <p class="text-gray-500">No items in cart</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($cart as $item): ?>
                                                                        <?php if (!isset($item['product']['name']) || !isset($item['quantity']) || !isset($item['price'])): ?>
                                        <div class="p-4 border border-red-200 bg-red-50 rounded-xl">
                                            <p class="text-red-600 text-sm">Invalid cart item data</p>
                                            <pre class="text-xs mt-2"><?php echo htmlspecialchars(print_r($item, true)); ?></pre>
                                        </div>
                                    <?php else: ?>
                                        <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-xl">
                                            <div class="flex-shrink-0">
                                                <?php if (!empty($item['product']['image']) && $item['product']['image'] !== 'N/A'): ?>
                                                    <img src="<?php echo htmlspecialchars($item['product']['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product']['name']); ?>" 
                                                         class="w-16 h-16 object-cover rounded-lg">
                                                <?php else: ?>
                                                    <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                                        <i class="fas fa-image text-gray-400"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-sm font-medium text-gray-900 truncate">
                                                    <?php echo htmlspecialchars($item['product']['name']); ?>
                                                </h3>
                                            </div>
                                    <div class="flex items-center space-x-3">
                                        <div class="text-sm text-gray-500">
                                            Qty: <?php echo $item['quantity']; ?>
                                        </div>
                                        <div class="text-right min-w-[4rem]">
                                            <p class="text-sm font-semibold text-gray-900">
                                                ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                            </p>
                                        </div>
                                    </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="border-t border-gray-200 pt-4 space-y-3">
                            <div class="flex justify-between text-neutral">
                                <span>Subtotal:</span>
                                <span>₹<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="flex justify-between text-neutral">
                                <span>Tax (18%):</span>
                                <span>₹<?php echo number_format($tax, 2); ?></span>
                            </div>
                            <div class="flex justify-between text-lg font-bold text-primary">
                                <span>Total:</span>
                                <span>₹<?php echo number_format($total, 2); ?></span>
                            </div>
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
            
            // Form validation
            const checkoutForm = document.querySelector('form[method="POST"]');
            if (checkoutForm) {
                checkoutForm.addEventListener('submit', function(e) {
                    const requiredFields = checkoutForm.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            isValid = false;
                            field.classList.add('border-red-500');
                        } else {
                            field.classList.remove('border-red-500');
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill in all required fields.');
                    }
                });
            }
        });
    </script>
</body>
</html>
