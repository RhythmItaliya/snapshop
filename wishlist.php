<?php
// Wishlist Page - Converted from React
// This page displays the user's wishlist with clear functionality

// Start session for user authentication
require_once __DIR__ . '/auth/auth-helper.php';
startSessionIfNotStarted();

// Include necessary files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/modal/wishlist.model.php';
require_once __DIR__ . '/component/ui/loading-spinner.php';
require_once __DIR__ . '/component/ui/error-state.php';
require_once __DIR__ . '/component/ui/button.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Initialize variables
$wishlist = [];
$loading = true;
$error = null;
$success = '';

// Handle clear wishlist action (only for logged-in users)
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear_wishlist') {
    try {
        $conn = getDatabaseConnection();
        if ($conn) {
            $wishlistModel = new Wishlist($conn);
            $wishlistModel->clearWishlist($_SESSION['user_id']);
            // Don't set success message, only use toast
            $conn->close();
        }
    } catch (Exception $e) {
        $error = 'Failed to clear wishlist: ' . $e->getMessage();
    }
}

// Fetch wishlist items (only for logged-in users)
if ($isLoggedIn) {
    try {
        $conn = getDatabaseConnection();
        if ($conn) {
            $wishlistModel = new Wishlist($conn);
            $wishlist = $wishlistModel->getWishlist($_SESSION['user_id']);
            $loading = false;
            $conn->close();
        } else {
            throw new Exception("Database connection failed");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        $loading = false;
    }
} else {
    // For non-logged-in users, show empty state
    $loading = false;
    $wishlist = [];
}

// Get wishlist count
$wishlistCount = count($wishlist);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - SnapShop</title>
    <link href="assets/css/tailwind.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Include Header -->
    <?php include 'component/header.php'; ?>
    
    <div>
        <div class="container mx-auto px-4 py-16">
            <!-- Header Section -->
            <div class="flex items-center justify-between mb-8 mt-8">
                <h1 class="text-3xl font-bold text-blue-600">My Wishlist</h1>
                <?php if ($isLoggedIn && $wishlistCount > 0): ?>
                    <button onclick="handleClearWishlist()" 
                            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm">
                        Clear Wishlist
                    </button>
                <?php endif; ?>
            </div>

            <!-- Loading State -->
            <?php if ($loading): ?>
                <div class="text-center py-20">
                    <?php echo renderLoadingSpinner(['size' => 'lg', 'variant' => 'primary']); ?>
                    <p class="mt-4 text-gray-600 text-lg">Loading wishlist...</p>
                </div>
            <?php endif; ?>

            <!-- Error State -->
            <?php if ($error): ?>
                <div class="text-center py-20">
                    <?php echo renderErrorState([
                        'error' => $error,
                        'onRetry' => 'window.location.reload()'
                    ]); ?>
                </div>
            <?php endif; ?>

            <!-- Wishlist Content -->
            <?php if (!$loading && !$error): ?>
                <?php if ($wishlistCount === 0): ?>
                    <!-- Empty Wishlist State -->
                    <div class="text-center py-16">
                        <h3 class="text-xl font-semibold text-blue-600 mb-2">Your Wishlist is Empty</h3>
                        <p class="text-gray-600 mb-6">Start adding products to your wishlist to see them here!</p>
                        <button onclick="window.history.back()" 
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors text-lg">
                            Continue Shopping
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Wishlist Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <?php foreach ($wishlist as $product): ?>
                            <div class="transform hover:scale-105 transition-transform duration-300">
                                <?php 
                                // Set the current product for the ProductCard component
                                $GLOBALS['currentProduct'] = $product;
                                ?>
                                <?php include 'component/product/product-card.php'; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include 'component/footer.php'; ?>

    <!-- Include Toast Component -->
    <?php include 'component/ui/toast.php'; ?>

    <!-- Include Auth Modals -->
    <?php include 'auth/login.php'; ?>
    <?php include 'auth/register.php'; ?>

    <!-- JavaScript for wishlist functionality -->
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

        // Clear wishlist functionality
        function handleClearWishlist() {
            const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
            
            if (!isLoggedIn) {
                if (typeof showToast === 'function') {
                    showToast('Please login to manage your wishlist', 'warning', 4000);
                } else {
                    alert('Please login to manage your wishlist');
                }
                
                // Open login modal after a short delay
                setTimeout(() => {
                    if (typeof openLoginModal === 'function') {
                        openLoginModal();
                    }
                }, 1000);
                return;
            }
            
            if (confirm('Are you sure you want to clear your wishlist? This action cannot be undone.')) {
                // Show warning toast
                if (typeof showToast === 'function') {
                    showToast('Clearing wishlist...', 'warning', 2000);
                }
                
                // Submit the form to clear wishlist
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="clear_wishlist">';
                document.body.appendChild(form);
                form.submit();
                
                // Show success toast after form submission
                setTimeout(() => {
                    if (typeof showToast === 'function') {
                        showToast('Wishlist cleared successfully!', 'success', 3000);
                    }
                }, 500);
            }
        }
    </script>
</body>
</html>
