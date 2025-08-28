<?php
// Admin Sidebar Component
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="fixed left-0 top-0 h-full w-64 bg-white shadow-lg border-r border-gray-200 z-30 overflow-y-auto">
    <nav class="p-4 h-full flex flex-col">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900">SnapShop Admin</h2>
        </div>
        
        <ul class="space-y-2 flex-1">
            <!-- Dashboard -->
            <li>
                <a href="/snapshop/admin/index.php" 
                   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors <?php echo $currentPage === 'index' ? 'bg-purple-50 text-purple-700 border-l-4 border-purple-500' : ''; ?>">
                    <svg class="w-5 h-5 mr-3 <?php echo $currentPage === 'index' ? 'text-purple-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    <span class="<?php echo $currentPage === 'index' ? 'font-semibold' : ''; ?>">Dashboard</span>
                </a>
            </li>
            
            <!-- Products -->
            <li>
                <a href="/snapshop/admin/products.php" 
                   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors <?php echo $currentPage === 'products' ? 'bg-purple-50 text-purple-700 border-l-4 border-purple-500' : ''; ?>">
                    <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <span>Products</span>
                </a>
            </li>
            
            <!-- Users -->
            <li>
                <a href="/snapshop/admin/users.php" 
                   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors <?php echo $currentPage === 'users' ? 'bg-purple-50 text-purple-700 border-l-4 border-purple-500' : ''; ?>">
                    <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <span>Users</span>
                </a>
            </li>
            
            <!-- Orders -->
            <li>
                <a href="/snapshop/admin/orders.php" 
                   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors <?php echo $currentPage === 'orders' ? 'bg-purple-50 text-purple-700 border-l-4 border-purple-500' : ''; ?>">
                    <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span>Orders</span>
                </a>
            </li>
            
            <!-- Contact Us -->
            <li>
                <a href="/snapshop/admin/contacts.php" 
                   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors <?php echo $currentPage === 'contacts' ? 'bg-purple-50 text-purple-700 border-l-4 border-purple-500' : ''; ?>">
                    <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span>Contact Us</span>
                </a>
            </li>
        </ul>
        
        <!-- Logout at bottom -->
        <div class="mt-auto pt-4">
            <a href="/snapshop/admin/auth/logout.php" 
               class="flex items-center px-4 py-3 text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</aside>
