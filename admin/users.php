<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Admin Users Management Page
session_start();

// Require admin authentication
require_once 'auth/admin-auth-helper.php';
AdminAuthHelper::requireAdminAuth();

// Get admin data from session
$admin = AdminAuthHelper::getAdminData();

// Include required files
require_once '../config/database.php';
require_once '../modal/user.model.php';

$userModel = new User(getDatabaseConnection());

try {
    $users = $userModel->getAllUsers();
} catch (Exception $e) {
    $error = "Error loading users: " . $e->getMessage();
    $users = [];
}

$success = '';
$error = '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - SnapShop Admin</title>
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
            <div class="w-full">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Manage Users</h1>
                    <p class="text-gray-600">View all registered users and their order history - Total Users: <span id="userCount"><?php echo count($users); ?></span></p>
                </div>
                
                <!-- Success Message -->
                <?php if (isset($_GET['success']) && $_GET['success'] === 'user_deleted'): ?>
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm text-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        User deleted successfully!
                    </div>
                <?php endif; ?>

                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($users)): ?>
                    <div id="noUsersMessage" class="text-center py-12">
                        <div class="mx-auto h-12 w-12 text-gray-400">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
                        <p class="mt-1 text-sm text-gray-500">Users will appear here when they register.</p>
                    </div>
                <?php else: ?>
                    <div id="usersTable">
                        <div class="bg-white shadow rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">All Users (<?php echo count($users); ?>)</h3>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent</th>

                                        </tr>
                                    </thead>
                                    <tbody id="usersTableBody" class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($users as $user): ?>
                                            <tr class="hover:bg-gray-50">
                                                <!-- Username + email column -->
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php 
                                                        $fullName = '';
                                                        if (!empty($user['first_name']) || !empty($user['last_name'])) {
                                                            $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                                                        } else {
                                                            $fullName = $user['username'] ?? 'N/A';
                                                        }
                                                        echo htmlspecialchars($fullName);
                                                        ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500">@<?php echo htmlspecialchars($user['username'] ?? 'unknown'); ?></div>
                                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($user['email'] ?? 'no-email'); ?></div>
                                                </td>
                                                
                                                <!-- Phone number column -->
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-900">
                                                        <?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?>
                                                    </div>
                                                </td>
                                                
                                                <!-- Orders column -->
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-medium text-blue-600">
                                                        <?php echo isset($user['total_orders']) ? $user['total_orders'] : 0; ?> Orders
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        <?php echo isset($user['cancelled_orders']) ? $user['cancelled_orders'] : 0; ?> Cancelled
                                                    </div>
                                                </td>

                                                <!-- Total spent column -->
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        ₹<?php echo number_format(isset($user['total_spent']) ? $user['total_spent'] : 0, 2); ?>
                                                    </div>
                                                </td>
                                                

                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Include Toast Component -->
    <?php include 'component/ui/toast.php'; ?>
    
    <!-- Include Admin Auth JavaScript -->
    <script src="assets/js/admin-auth.js"></script>
</body>
</html>
