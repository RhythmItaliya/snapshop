<?php
// Admin Orders Management Page
require_once __DIR__ . '/../auth/auth-helper.php';
startSessionIfNotStarted();

// Require admin authentication
require_once 'auth/admin-auth-helper.php';
AdminAuthHelper::requireAdminAuth();

// Get admin data from session
$admin = AdminAuthHelper::getAdminData();

// Include required files
require_once '../config/database.php';
require_once '../modal/order.model.php';

$orderModel = new Order(getDatabaseConnection());
$orders = $orderModel->getAllOrders();

$success = '';
$error = '';

// Handle order status update
if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['new_status'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['new_status'];
    
    try {
        if ($orderModel->updateOrderStatus($orderId, $newStatus)) {
            $success = "Order status updated successfully!";
            $orders = $orderModel->getAllOrders(); // Refresh the list
        } else {
            $error = "Failed to update order status.";
        }
    } catch (Exception $e) {
        $error = "Error updating order status: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - SnapShop Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../node_modules/aos/dist/aos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-50">
    <div class="flex pt-4">
        <!-- Admin Sidebar -->
        <?php include 'component/sidebar.php'; ?>
        
        <!-- Main Content Area -->
        <main class="flex-1 ml-64 bg-gray-100 min-h-screen">
            <div class="w-full p-8 pt-20 max-w-7xl mx-auto">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-900">Manage Orders</h1>
                        <p class="text-gray-600">View and update order statuses - Total Orders: <?php echo count($orders); ?></p>
                    </div>
                    
                    <!-- Success Message -->
                    <?php if ($success): ?>
                        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm text-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Error Message -->
                    <?php if ($error): ?>
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($orders)): ?>
                        <div class="bg-white shadow rounded-lg overflow-hidden w-full">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">All Orders (<?php echo count($orders); ?>)</h3>
                            </div>
                            
                            <div class="overflow-x-auto w-full">
                                <table class="w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Items</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($orders as $order): ?>
                                            <tr class="hover:bg-gray-50">
                                                <!-- Order ID Column -->
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        #<?php echo substr(strval($order['id']), -8); ?>
                                                    </div>
                                                    <?php if (isset($order['order_number'])): ?>
                                                        <div class="text-xs text-blue-600 font-mono">
                                                            <?php echo htmlspecialchars($order['order_number']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>

                                                <!-- Email ID Column -->
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-900">
                                                        <?php echo htmlspecialchars($order['user_email'] ?? 'N/A'); ?>
                                                    </div>
                                                </td>

                                                <!-- Payment Status Column -->
                                                <td class="px-6 py-4">
                                                    <div class="space-y-1">
                                                        <?php if (isset($order['payment_id']) && $order['payment_id']): ?>
                                                            <div class="text-xs text-green-600 font-medium">
                                                                ✓ Paid
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="text-xs text-red-600 font-medium">
                                                                ✗ Unpaid
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="text-xs text-gray-500">
                                                            ₹<?php echo number_format($order['total_amount'] ?? 0, 2); ?>
                                                        </div>
                                                        <div class="text-xs text-gray-500">
                                                            <?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Product Items Column -->
                                                <td class="px-6 py-4">
                                                    <div class="space-y-1">
                                                        <div class="text-xs text-gray-600">
                                                            <?php if (isset($order['items']) && !empty($order['items'])): ?>
                                                                <?php foreach ($order['items'] as $item): ?>
                                                                    <div class="flex justify-between items-center mb-1">
                                                                        <span class="text-sm"><?php echo htmlspecialchars($item['product_name'] ?? 'Product'); ?></span>
                                                                        <span class="text-xs text-gray-500">x<?php echo $item['quantity'] ?? 1; ?></span>
                                                                        <span class="text-xs text-blue-600">₹<?php echo number_format($item['price'] ?? 0, 2); ?></span>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <span class="text-gray-500">No products</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                
                                                <!-- Actions Column -->
                                                <td class="px-6 py-4">
                                                    <div class="space-y-2">
                                                        <form method="POST" class="inline">
                                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                                                                                    <select 
                                                            name="new_status"
                                                            class="order-status-select w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                                            data-order-id="<?php echo $order['id']; ?>"
                                                            data-current-status="<?php echo $order['status'] ?? 'placed'; ?>"
                                                            onchange="updateOrderStatus(this)">
                                                                <option value="placed" <?php echo ($order['status'] ?? 'placed') === 'placed' ? 'selected' : ''; ?>>Placed</option>
                                                                <option value="confirmed" <?php echo ($order['status'] ?? 'placed') === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                                <option value="processing" <?php echo ($order['status'] ?? 'placed') === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                                <option value="shipped" <?php echo ($order['status'] ?? 'placed') === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                                <option value="delivered" <?php echo ($order['status'] ?? 'placed') === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                                <option value="cancelled" <?php echo ($order['status'] ?? 'placed') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                            </select>
                                                            <input type="hidden" name="update_status" value="1">
                                                        </form>
                                                        <div class="text-xs text-gray-500 mt-1 current-status">
                                                            <?php echo htmlspecialchars($order['status'] ?? 'placed'); ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="mx-auto h-12 w-12 text-gray-400">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No orders found</h3>
                            <p class="mt-1 text-sm text-gray-500">Orders will appear here when customers place them.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Include Toast Component -->
    <?php include 'component/ui/toast.php'; ?>
    
    <!-- Include Admin Auth JavaScript -->
    <script src="assets/js/admin-auth.js"></script>

    <script>
    function updateOrderStatus(selectElement) {
        const orderId = selectElement.dataset.orderId;
        const newStatus = selectElement.value;
        const currentStatus = selectElement.dataset.currentStatus;
        
        if (newStatus === currentStatus) return;
        
        // Confirm the status change
        if (confirm(`Are you sure you want to change the order status from "${currentStatus}" to "${newStatus}"?`)) {
            // Submit the form
            const form = selectElement.closest('form');
            if (form) {
                form.submit();
            }
        } else {
            // Reset to previous status if user cancels
            selectElement.value = currentStatus;
        }
    }
    </script>
</body>
</html>
