<?php
// User Orders Page

// Start session for user authentication
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: /snapshop/auth/login.php');
    exit;
}

// Include necessary files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/modal/user.model.php';
require_once __DIR__ . '/component/ui/loading-spinner.php';
require_once __DIR__ . '/component/ui/error-state.php';
require_once __DIR__ . '/component/ui/button.php';
require_once __DIR__ . '/component/ui/toast.php';

// Initialize variables
$user = null;
$loading = true;
$error = null;

// Fetch user data
try {
    $conn = getDatabaseConnection();
    if ($conn) {
        $userModel = new User($conn);
        $user = $userModel->getUserById($_SESSION['user_id']);
        $loading = false;
        $conn->close();
    } else {
        throw new Exception("Database connection failed");
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    $loading = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - SnapShop</title>
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
                <p class="mt-4 text-gray-600">Loading orders...</p>
            </div>
        <?php elseif ($error): ?>
            <!-- Error State -->
            <div class="text-center py-20">
                <?php echo renderErrorState([
                    'error' => $error,
                    'onRetry' => 'window.location.reload()'
                ]); ?>
            </div>
        <?php elseif ($user): ?>
            <div class="container mx-auto px-4 py-6">
                <div class="max-w-5xl mx-auto">
                    <?php 
                    // Set page variables for common header
                    $pageTitle = 'My Orders';
                    $pageDescription = 'View your order history and track current orders';
                    $showBackButton = true;
                    $backUrl = '/snapshop/profile.php';
                    $backText = 'Back to Profile';
                    
                    // Include common profile header
                    include 'component/profile-header.php';
                    ?>

                    <!-- Orders Content -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <!-- Orders Coming Soon -->
                        <div class="text-center py-12">
                            <i class="fas fa-shopping-bag text-4xl text-blue-500 mb-4"></i>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Order History Coming Soon</h3>
                            <p class="text-gray-600 mb-6">We're working on bringing you a complete order history feature. You'll be able to view all your past orders, track current shipments, and manage your shopping history.</p>
                            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                <a href="/snapshop/products.php" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-shopping-cart mr-2"></i>
                                    Start Shopping
                                </a>
                                <a href="/snapshop/profile.php" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                    <i class="fas fa-user mr-2"></i>
                                    Back to Profile
                                </a>
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
</body>
</html>
