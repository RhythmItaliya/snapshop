<?php
// User Orders Page

// Start session for user authentication
require_once __DIR__ . '/auth/auth-helper.php';
startSessionIfNotStarted();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /snapshop/');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/modal/user.model.php';
require_once __DIR__ . '/modal/order.model.php';
require_once __DIR__ . '/component/ui/loading-spinner.php';
require_once __DIR__ . '/component/ui/error-state.php';
require_once __DIR__ . '/component/ui/button.php';
require_once __DIR__ . '/component/ui/toast.php';

$user = null;
$error = null;

$status = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : 'all';

// Handle cancel action server-side (no API needed)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    try {
        $conn = getDatabaseConnection();
        if ($conn) {
            $orderModel = new Order($conn);
            $orderId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($orderId > 0) {
                $ok = $orderModel->cancelOrder($orderId, $_SESSION['user_id']);
                if ($ok) {
                    addToast('Order cancelled successfully!', 'success', 3000);
                } else {
                    addToast('Cannot cancel this order', 'error', 3000);
                }
            }
            $conn->close();
        }
    } catch (Exception $e) {
        addToast($e->getMessage(), 'error', 4000);
    }
    
    // Redirect to prevent form resubmission
    $redirectUrl = '/snapshop/orders.php';
    if (isset($_GET['status']) && $_GET['status'] !== '') {
        $redirectUrl .= '?status=' . urlencode($_GET['status']);
    }
    header('Location: ' . $redirectUrl);
    exit;
}

// Fetch orders server-side
$orders = [];
try {
    $conn = getDatabaseConnection();
    if ($conn) {
        $orderModel = new Order($conn);
        $orders = $orderModel->getUserOrdersWithItems($_SESSION['user_id'], $status);
        $conn->close();
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - SnapShop</title>
    <link href="assets/css/tailwind.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-50">
    <?php include 'component/header.php'; ?>

    <div class="pt-20">
        <?php if ($error): ?>
            <div class="text-center py-20">
                <?php echo renderErrorState(['error' => $error, 'onRetry' => 'window.location.reload()']); ?>
            </div>
        <?php else: ?>
            <div class="container mx-auto px-4 py-6">
                <div class="max-w-5xl mx-auto">
                    <?php 
                    $pageTitle = 'My Orders';
                    $pageDescription = 'View your order history and track current orders';
                    include 'component/profile-header.php';
                    ?>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" id="ordersRoot">
                        <div class="mb-6">
                            <form method="GET" class="inline-flex items-center space-x-2">
                                <label for="statusFilter" class="text-sm text-gray-600">Filter:</label>
                                <select id="statusFilter" name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" onchange="this.form.submit()">
                                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Orders</option>
                                    <option value="placed" <?php echo $status === 'placed' ? 'selected' : ''; ?>>Order Placed</option>
                                    <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Order Confirmed</option>
                                    <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </form>
                        </div>

                        <?php if (empty($orders)): ?>
                            <div class="text-center py-12">
                                <i class="fas fa-box text-4xl text-gray-400 mb-4"></i>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">No orders found</h3>
                                <p class="text-gray-600 mb-6">
                                    <?php echo $status === 'all' ? "You haven't placed any orders yet." : 'No orders with status "' . htmlspecialchars($status) . '" found.'; ?>
                                </p>
                                <a href="/snapshop/products.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-shopping-cart mr-2"></i>
                                    <span>Start Shopping</span>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($orders as $order): 
                                    $conf = [
                                        'placed' => ['label' => 'Order Placed', 'icon' => 'fa-box', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50', 'border' => 'border-blue-100'],
                                        'confirmed' => ['label' => 'Order Confirmed', 'icon' => 'fa-check-circle', 'color' => 'text-green-600', 'bg' => 'bg-green-50', 'border' => 'border-green-100'],
                                        'processing' => ['label' => 'Processing', 'icon' => 'fa-clock', 'color' => 'text-indigo-600', 'bg' => 'bg-indigo-50', 'border' => 'border-indigo-100'],
                                        'shipped' => ['label' => 'Shipped', 'icon' => 'fa-truck', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50', 'border' => 'border-blue-100'],
                                        'delivered' => ['label' => 'Delivered', 'icon' => 'fa-check-circle', 'color' => 'text-green-600', 'bg' => 'bg-green-50', 'border' => 'border-green-100'],
                                        'cancelled' => ['label' => 'Cancelled', 'icon' => 'fa-times-circle', 'color' => 'text-red-600', 'bg' => 'bg-red-50', 'border' => 'border-red-100'],
                                    ][$order['status']] ?? ['label' => 'Order Placed', 'icon' => 'fa-box', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50', 'border' => 'border-blue-100'];
                                ?>
                                    <div class="bg-white rounded-lg border <?php echo $conf['border']; ?> shadow-sm hover:shadow-md transition-shadow">
                                        <div class="p-4 border-b border-gray-100">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <div class="p-2 rounded-full <?php echo $conf['bg']; ?>">
                                                        <i class="fas <?php echo $conf['icon']; ?> <?php echo $conf['color']; ?>"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-medium text-gray-900">Order #<?php echo htmlspecialchars($order['order_number'] ?? $order['id']); ?></h4>
                                                        <p class="text-sm text-gray-500">Placed on <?php echo date('M j, Y', strtotime($order['created_at'])); ?></p>
                                                    </div>
                                                </div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $conf['bg'] . ' ' . $conf['color']; ?>"><?php echo $conf['label']; ?></span>
                                            </div>
                                        </div>
                                        <div class="p-4">
                                            <div class="space-y-3">
                                                <?php foreach (array_slice($order['items'] ?? [], 0, 2) as $item): ?>
                                                    <div class="flex items-center space-x-3">
                                                        <?php if (!empty($item['product_image'])): ?>
                                                            <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name'] ?? 'Product'); ?>" class="w-12 h-12 object-cover rounded-md">
                                                        <?php else: ?>
                                                            <div class="w-12 h-12 bg-gray-200 rounded-md flex items-center justify-center">
                                                                <i class="fas fa-image text-gray-400"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-medium text-gray-900 truncate"><?php echo htmlspecialchars($item['product_name'] ?? 'Product'); ?></p>
                                                            <p class="text-sm text-gray-500">Qty: <?php echo (int)$item['quantity']; ?> × ₹<?php echo number_format((float)$item['price'], 2); ?></p>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php if (!empty($order['items']) && count($order['items']) > 2): ?>
                                                    <p class="text-sm text-gray-500 text-center">+<?php echo count($order['items']) - 2; ?> more items</p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                                                <div class="text-sm text-gray-600">
                                                    <p>Total: <span class="font-semibold">₹<?php echo number_format((float)$order['total_amount'], 2); ?></span></p>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <?php 
                                                    $orderStatus = $order['status'] ?? 'unknown';
                                                    $canCancel = in_array($orderStatus, ['placed', 'processing', 'confirmed']);
                                                    ?>
                                                    
                                                    <?php if ($canCancel): ?>
                                                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this order? This action cannot be undone.');" class="inline">
                                                            <input type="hidden" name="action" value="cancel">
                                                            <input type="hidden" name="id" value="<?php echo (int)$order['id']; ?>">
                                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-gray-700 rounded text-sm font-medium">
                                                                Cancel
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                        
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'component/footer.php'; ?>
    <?php include 'component/ui/toast.php'; ?>
    <?php include 'auth/login.php'; ?>
    <?php include 'auth/register.php'; ?>

    <script>
        const statusConfig = {
            placed: { label: 'Order Placed', icon: 'fa-box', color: 'text-blue-600', bgColor: 'bg-blue-50', borderColor: 'border-blue-100' },
            confirmed: { label: 'Order Confirmed', icon: 'fa-check-circle', color: 'text-green-600', bgColor: 'bg-green-50', borderColor: 'border-green-100' },
            processing: { label: 'Processing', icon: 'fa-clock', color: 'text-indigo-600', bgColor: 'bg-indigo-50', borderColor: 'border-indigo-100' },
            shipped: { label: 'Shipped', icon: 'fa-truck', color: 'text-blue-600', bgColor: 'bg-blue-50', borderColor: 'border-blue-100' },
            delivered: { label: 'Delivered', icon: 'fa-check-circle', color: 'text-green-600', bgColor: 'bg-green-50', borderColor: 'border-green-100' },
            cancelled: { label: 'Cancelled', icon: 'fa-times-circle', color: 'text-red-600', bgColor: 'bg-red-50', borderColor: 'border-red-100' },
        };

        function getStatusConfig(status) { return statusConfig[status] || statusConfig.placed; }

        async function fetchOrders() {
            const filter = document.getElementById('statusFilter').value;
            const loading = document.getElementById('ordersLoading');
            const errorEl = document.getElementById('ordersError');
            const emptyEl = document.getElementById('ordersEmpty');
            const listEl = document.getElementById('ordersList');
            const emptyMsg = document.getElementById('emptyMsg');

            const url = `/snapshop/api/orders.php?status=${encodeURIComponent(filter)}`;
            console.log('[Orders] Fetch start:', { filter, url });

            loading.classList.remove('hidden');
            errorEl.classList.add('hidden');
            emptyEl.classList.add('hidden');
            listEl.innerHTML = '';

            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                console.log('[Orders] Response status:', res.status, res.statusText);
                let data;
                try {
                    data = await res.json();
                } catch (jsonErr) {
                    console.error('[Orders] Failed to parse JSON:', jsonErr);
                    throw new Error('Invalid JSON from API');
                }
                console.log('[Orders] Response JSON:', data);
                if (!res.ok || !data.success) {
                    const msg = (data && data.message) ? data.message : `HTTP ${res.status}`;
                    throw new Error(msg);
                }

                const orders = data.orders || [];
                console.log('[Orders] Orders count:', orders.length);
                if (orders.length === 0) {
                    emptyEl.classList.remove('hidden');
                    await updateEmptyState(filter);
                    if (typeof showToast === 'function') {
                        const msg = filter === 'all' ? "No orders found. Start shopping to place your first order." : `No orders with status "${getStatusConfig(filter).label}" found.`;
                        showToast(msg, 'info', 3500);
                    }
                    return;
                }

                orders.forEach(order => {
                    const conf = getStatusConfig(order.status);
                    const wrapper = document.createElement('div');
                    wrapper.className = `bg-white rounded-lg border ${conf.borderColor} shadow-sm hover:shadow-md transition-shadow`;
                    wrapper.innerHTML = `
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 rounded-full ${conf.bgColor}">
                                        <i class="fas ${conf.icon} ${conf.color}"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Order #${order.order_number || order.id}</h4>
                                        <p class="text-sm text-gray-500">Placed on ${new Date(order.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${conf.bgColor} ${conf.color}">${conf.label}</span>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                ${(order.items || []).slice(0,2).map(item => `
                                    <div class="flex items-center space-x-3">
                                        ${item.product_image ? 
                                            `<img src="${item.product_image}" alt="${item.product_name || 'Product'}" class="w-12 h-12 object-cover rounded-md">` :
                                            `<div class="w-12 h-12 bg-gray-200 rounded-md flex items-center justify-center">
                                                <i class="fas fa-image text-gray-400"></i>
                                            </div>`
                                        }
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">${item.product_name || 'Product'}</p>
                                            <p class="text-sm text-gray-500">Qty: ${item.quantity} × ₹${Number(item.price).toFixed(2)}</p>
                                        </div>
                                    </div>
                                `).join('')}
                                ${(order.items || []).length > 2 ? `<p class="text-sm text-gray-500 text-center">+${(order.items || []).length - 2} more items</p>` : ''}
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    <p>Total: <span class="font-semibold">₹${Number(order.total_amount).toFixed(2)}</span></p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    ${['placed','processing','confirmed'].includes(order.status) ? 
                                        `<button data-cancel-id="${order.id}" class="inline-flex items-center px-4 py-2 border border-red-500 text-red-600 rounded-lg hover:bg-red-600 hover:text-white text-sm font-medium transition-colors duration-200">
                                            <i class="fas fa-times mr-2"></i>
                                            Cancel
                                        </button>` : ''
                                    }
                                    ${order.status === 'cancelled' ? 
                                        `<span class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                                            <i class="fas fa-ban mr-1"></i>
                                            Cancelled
                                        </span>` : ''
                                    }
                                </div>
                            </div>
                        </div>`;

                    listEl.appendChild(wrapper);
                });

                // Attach cancel handlers
                document.querySelectorAll('[data-cancel-id]').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        const id = e.currentTarget.getAttribute('data-cancel-id');
                        console.log('[Orders] Cancel request for id:', id);
                        if (!confirm('Are you sure you want to cancel this order? This action cannot be undone.')) return;
                        try {
                            const form = new FormData();
                            form.append('action', 'cancel');
                            form.append('id', id);
                            const res = await fetch('/snapshop/api/orders.php', { method: 'POST', body: form });
                            console.log('[Orders] Cancel response status:', res.status, res.statusText);
                            let data;
                            try { data = await res.json(); } catch (je) { console.error('[Orders] Cancel JSON parse error:', je); throw new Error('Invalid JSON from cancel API'); }
                            console.log('[Orders] Cancel response JSON:', data);
                            if (!res.ok || !data.success) throw new Error(data.message || 'Failed to cancel order');
                            if (typeof showToast === 'function') { showToast('Order cancelled successfully!', 'success', 3000); }
                            fetchOrders();
                        } catch (err) {
                            console.error('[Orders] Cancel error:', err);
                            if (typeof showToast === 'function') { showToast(err.message || 'Failed to cancel order. Please try again.', 'error', 4000); }
                        }
                    });
                });

            } catch (err) {
                console.error('[Orders] Fetch error:', err);
                errorEl.classList.remove('hidden');
                if (typeof showToast === 'function') { showToast('Failed to load orders. Please try again.', 'error', 3000); }
            } finally {
                loading.classList.add('hidden');
                console.log('[Orders] Fetch end');
            }
        }

        async function fetchCartCount() {
            try {
                const res = await fetch('/snapshop/api/cart-items.php');
                const data = await res.json();
                if (!data || !Array.isArray(data)) return 0;
                // if API returns array of items, sum quantities
                return data.reduce((sum, item) => sum + (parseInt(item.quantity, 10) || 0), 0);
            } catch (e) {
                return 0;
            }
        }

        async function updateEmptyState(filter) {
            const titleEl = document.getElementById('emptyTitle');
            const msgEl = document.getElementById('emptyMsg');
            const badgeEl = document.getElementById('cartCountBadge');
            if (filter && filter !== 'all') {
                titleEl.textContent = 'Order not found';
                msgEl.textContent = `No orders with status "${getStatusConfig(filter).label}" found.`;
            } else {
                titleEl.textContent = "Order not found";
                msgEl.textContent = "You haven't placed any orders yet.";
            }
            const count = await fetchCartCount();
            if (count > 0) {
                badgeEl.textContent = `${count}`;
                badgeEl.classList.remove('hidden');
            } else {
                badgeEl.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const filterEl = document.getElementById('statusFilter');
            const urlParams = new URLSearchParams(window.location.search);
            const initialStatus = (urlParams.get('status') || 'all');
            if (filterEl) {
                filterEl.value = initialStatus;
            }
            filterEl.addEventListener('change', () => {
                const val = filterEl.value;
                const url = new URL(window.location.href);
                url.searchParams.set('status', val);
                window.history.replaceState({}, '', url);
                const navLink = document.getElementById('ordersNavLink');
                if (navLink) {
                    navLink.href = `/snapshop/orders.php?status=${encodeURIComponent(val)}`;
                }
                fetchOrders();
            });
            fetchOrders();
            updateEmptyState(initialStatus);
        });
    </script>
</body>
</html>
