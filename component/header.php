<?php
session_start();
require_once __DIR__ . '/../auth/auth-helper.php';
// Header Component - with authentication logic
?>
<header class="bg-white shadow-sm fixed w-full z-50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <!-- Logo and Brand -->
            <a href="/snapshop/" class="flex items-center space-x-2">
                <img src="assets/img/logo.svg" alt="Logo" class="w-8 h-8" />
                <span class="text-xl font-bold text-gray-800">SnapShop</span>
            </a>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex space-x-6">
                <a href="/snapshop/" class="text-primary relative group">
                    Home
                    <span class="absolute bottom-0 left-0 h-0.5 bg-secondary w-full"></span>
                </a>
                <a href="/snapshop/products.php?category=men" class="text-gray-600 hover:text-secondary relative group">
                    Men
                    <span class="absolute bottom-0 left-0 h-0.5 bg-secondary w-0 group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="/snapshop/products.php?category=women" class="text-gray-600 hover:text-secondary relative group">
                    Women
                    <span class="absolute bottom-0 left-0 h-0.5 bg-secondary w-0 group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="/snapshop/products.php?category=sale" class="text-gray-600 hover:text-secondary relative group">
                    Sale
                    <span class="absolute bottom-0 left-0 h-0.5 bg-secondary w-0 group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="/snapshop/about" class="text-gray-600 hover:text-secondary relative group">
                    About Us
                    <span class="absolute bottom-0 left-0 h-0.5 bg-secondary w-0 group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="/snapshop/contact" class="text-gray-600 hover:text-secondary relative group">
                    Contact Us
                    <span class="absolute bottom-0 left-0 h-0.5 bg-secondary w-0 group-hover:w-full transition-all duration-300"></span>
                </a>
            </nav>

            <!-- Right Side Actions -->
            <div class="flex items-center space-x-4">
                <!-- Mobile Menu Button -->
                <button class="md:hidden p-2 text-gray-600 hover:text-secondary transition-colors" id="mobileMenuBtn">
                    <i class="fas fa-bars w-5 h-5"></i>
                </button>

                <?php if (isUserLoggedIn()): ?>
                    <!-- User Menu (Logged In) -->
                    <div class="hidden sm:flex items-center space-x-3">
                        <button onclick="handleLogout()" class="text-red-600 hover:text-red-700 relative group">
                            Logout
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-red-600 group-hover:w-full transition-all duration-300"></span>
                        </button>
                    </div>
                    
                    <!-- Profile Icon (Logged In) -->
                    <div class="hidden sm:block">
                        <a href="/snapshop/profile.php" class="p-2 text-gray-600 hover:text-secondary transition-colors">
                            <i class="fas fa-user w-5 h-5"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Sign In/Sign Up Buttons (Not Logged In) -->
                    <div class="hidden sm:flex space-x-3">
                        <button onclick="openLoginModal()" class="text-gray-600 hover:text-secondary relative group">
                            Sign In
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-secondary group-hover:w-full transition-all duration-300"></span>
                        </button>
                        <button onclick="openRegisterModal()" class="text-gray-600 hover:text-secondary relative group">
                            Sign Up
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-secondary group-hover:w-full transition-all duration-300"></span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Wishlist Icon -->
                <a href="/snapshop/wishlist.php" class="relative p-2 text-gray-600 hover:text-red-500 transition-colors">
                    <i class="fas fa-heart w-5 h-5"></i>
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                        <?php 
                        if (isset($_SESSION['user_id'])) {
                            require_once __DIR__ . '/../modal/wishlist.model.php';
                            require_once __DIR__ . '/../config/database.php';
                            $conn = getDatabaseConnection();
                            if ($conn) {
                                $wishlistModel = new Wishlist($conn);
                                echo $wishlistModel->getWishlistCount($_SESSION['user_id']);
                                $conn->close();
                            } else {
                                echo '0';
                            }
                        } else {
                            echo '0';
                        }
                        ?>
                    </span>
                </a>

                <!-- Cart Icon -->
                <button class="relative p-2 text-gray-600 hover:text-secondary transition-colors">
                    <i class="fas fa-shopping-cart w-5 h-5"></i>
                    <span class="absolute -top-2 -right-2 bg-secondary text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                        0
                    </span>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="md:hidden py-4 border-t hidden" id="mobileMenu">
            <div class="space-y-3">
                <a href="/snapshop/" class="block w-full text-left text-primary relative group">
                    Home
                    <span class="absolute bottom-0 left-0 h-0.5 bg-secondary w-full"></span>
                </a>
                <a href="/snapshop/products.php?category=men" class="block w-full text-left text-gray-600 hover:text-secondary relative group">
                    Men
                    <span class="absolute bottom-0 left-0 h-0.5 bg-secondary w-0 group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="/snapshop/products.php?category=women" class="block w-full text-left text-gray-600 hover:text-secondary relative group">
                    Women
                    <span class="absolute bottom-0 left-0 h-0.5 bg-secondary w-0 group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="/snapshop/products.php?category=sale" class="block w-full text-left text-gray-600 hover:text-secondary relative group">
                    Sale
                    <span class="absolute bottom-0 left-0 h-0.5 bg-secondary w-0 group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="/about" class="block w-full text-left text-gray-600 hover:text-secondary relative group">
                    About Us
                    <span class="absolute bottom-0 left-0 h-0.5 bg-secondary w-0 group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="/contact" class="block w-full text-left text-gray-600 hover:text-secondary relative group">
                    Contact Us
                    <span class="absolute bottom-0 left-0 h-0.5 bg-secondary w-0 group-hover:w-full transition-all duration-300"></span>
                </a>
                
                <!-- Mobile Wishlist Link -->
                <a href="/snapshop/wishlist.php" class="block w-full text-left text-gray-600 hover:text-red-500 relative group">
                    Wishlist
                    <span class="absolute bottom-0 left-0 h-0.5 bg-red-500 w-0 group-hover:w-full transition-all duration-300"></span>
                </a>
                
                <?php if (isUserLoggedIn()): ?>
                    <!-- Mobile User Menu (Logged In) -->
                    <div class="pt-3 space-y-2">
                        <a href="/snapshop/profile.php" class="block w-full text-left text-gray-600 hover:text-secondary relative group">
                            Profile
                            <span class="absolute bottom-0 left-0 h-0.5 bg-secondary w-0 group-hover:w-full transition-all duration-300"></span>
                        </a>
                        <button onclick="handleLogout()" class="block w-full text-left text-red-600 hover:text-red-700 relative group">
                            Logout
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-red-600 group-hover:w-full transition-all duration-300"></span>
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Mobile Sign In/Sign Up -->
                    <div class="pt-3 space-y-2">
                        <button onclick="openLoginModal()" class="block w-full text-left text-gray-600 hover:text-secondary relative group">
                            Sign In
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-secondary group-hover:w-full transition-all duration-300"></span>
                        </button>
                        <button onclick="openRegisterModal()" class="block w-full text-left text-gray-600 hover:text-secondary relative group">
                            Sign Up
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-secondary group-hover:w-full transition-all duration-300"></span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- JavaScript for mobile menu toggle and logout handling -->
<script>
document.getElementById('mobileMenuBtn').addEventListener('click', function() {
    const mobileMenu = document.getElementById('mobileMenu');
    mobileMenu.classList.toggle('hidden');
});

// Handle logout with localStorage clearing
function handleLogout() {
    fetch('/snapshop/auth/logout.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear localStorage
                localStorage.removeItem('token');
                localStorage.removeItem('user_id');
                localStorage.removeItem('username');
                localStorage.removeItem('email');
                
                // Show success message
                if (typeof showToast === 'function') {
                    showToast('Logged out successfully', 'success', 3000);
                }
                
                // Refresh page to update header
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        })
        .catch(error => {
            console.error('Logout error:', error);
            // Force logout by clearing localStorage and refreshing
            localStorage.clear();
            window.location.reload();
        });
}

// Check if user is logged in on page load
document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('token');
    const userId = localStorage.getItem('user_id');
    
    if (token && userId) {
        // User has token in localStorage, check if session is still valid
        // This helps maintain login state across page refreshes
        console.log('User token found in localStorage');
        
        // Verify token with server
        verifyTokenWithServer(token);
    }
});

// Function to verify token with server
function verifyTokenWithServer(token) {
    fetch('/snapshop/auth/verify-token.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ token: token })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.valid) {
            // Token is invalid, clear localStorage
            localStorage.clear();
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Token verification error:', error);
        // On error, clear localStorage to be safe
        localStorage.clear();
        window.location.reload();
    });
}
</script>
