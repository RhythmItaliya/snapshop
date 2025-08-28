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
        
        // Get cart data
        $cartModel = new Cart($conn);
        $cart = $cartModel->getCartItems($_SESSION['user_id']);
        
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
            </div>
        <?php else: ?>
            <!-- Checkout Content -->
            <div class="container mx-auto px-4 py-16">
                <h1 class="text-3xl font-bold text-primary mb-8">Checkout</h1>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Shipping Information Form -->
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-primary mb-6">Shipping Information</h2>

                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="checkout_form" value="1">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php echo renderInput([
                                    'label' => 'First Name',
                                    'name' => 'firstName',
                                    'value' => htmlspecialchars($user['first_name'] ?? ''),
                                    'required' => true
                                ]); ?>
                                
                                <?php echo renderInput([
                                    'label' => 'Last Name',
                                    'name' => 'lastName',
                                    'value' => htmlspecialchars($user['last_name'] ?? ''),
                                    'required' => true
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
                                'value' => htmlspecialchars($user['address']['street_address'] ?? ''),
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
                            <?php foreach ($cart as $item): ?>
                                <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-xl">
                                    <div class="flex-shrink-0">
                                        <img src="<?php echo htmlspecialchars($item['image'] ?? 'assets/img/placeholder.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="w-16 h-16 object-cover rounded-lg">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-medium text-gray-900 truncate">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            Size: <?php echo htmlspecialchars($item['size'] ?? 'N/A'); ?> | 
                                            Color: <?php echo htmlspecialchars($item['color'] ?? 'N/A'); ?>
                                        </p>
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
                            <?php endforeach; ?>
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
        });
    </script>
</body>
</html>
