<?php
// Admin Panel Main Entry Point
session_start();

// Require admin authentication
require_once 'auth/admin-auth-helper.php';
AdminAuthHelper::requireAdminAuth();

// Get admin data from session
$admin = AdminAuthHelper::getAdminData();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SnapShop</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../node_modules/aos/dist/aos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-50">
    <div class="flex">
        <!-- Admin Sidebar -->
        <?php include 'component/sidebar.php'; ?>
        
        <!-- Main Content Area -->
        <main class="flex-1 ml-64 p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Success Message from URL -->
                <?php if (isset($_GET['success']) && $_GET['success'] === 'product_created'): ?>
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm text-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        Product created successfully!
                    </div>
                <?php endif; ?>
                
                <!-- Dashboard Content -->
                <?php include 'component/home/dashboard.php'; ?>
            </div>
        </main>
    </div>

    <!-- Include Toast Component -->
    <?php include 'component/ui/toast.php'; ?>
    
    <!-- Include Admin Auth JavaScript -->
    <script src="assets/js/admin-auth.js"></script>
</body>
</html>
