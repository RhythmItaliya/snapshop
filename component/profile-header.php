<?php
// Common Profile Header Component
// This component should be included in all profile-related pages

// Get the current page title and description
$pageTitle = $pageTitle ?? 'Profile';
$pageDescription = $pageDescription ?? 'Manage your account settings';

// Determine current status query if present
$currentStatus = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : 'all';
$ordersHref = '/snapshop/orders.php?status=' . urlencode($currentStatus);
?>

<!-- Page Header -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-blue-600"><?php echo htmlspecialchars($pageTitle); ?></h1>
            <p class="text-gray-600 text-sm mt-1"><?php echo htmlspecialchars($pageDescription); ?></p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="/snapshop/auth/logout.php" 
               class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors flex items-center space-x-2">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </div>
</div>

<!-- Navigation -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
    <div class="flex space-x-1">
        <a href="/snapshop/profile.php" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $pageTitle === 'My Profile' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'; ?>">
            <i class="fas fa-user mr-2"></i>
            Profile
        </a>
        <a id="ordersNavLink" href="<?php echo htmlspecialchars($ordersHref); ?>" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $pageTitle === 'My Orders' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'; ?>">
            <i class="fas fa-shopping-bag mr-2"></i>
            My Orders
        </a>
        <a href="/snapshop/change-password.php" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $pageTitle === 'Change Password' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'; ?>">
            <i class="fas fa-lock mr-2"></i>
            Change Password
        </a>
    </div>
</div>
