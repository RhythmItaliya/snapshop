<?php
// Header Component - UI only, no functionality
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

                <!-- Sign In/Sign Up Buttons (Not Logged In) -->
                <div class="hidden sm:flex space-x-3">
                    <button class="text-gray-600 hover:text-secondary relative group">
                        Sign In
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-secondary group-hover:w-full transition-all duration-300"></span>
                    </button>
                    <button class="text-gray-600 hover:text-secondary relative group">
                        Sign Up
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-secondary group-hover:w-full transition-all duration-300"></span>
                    </button>
                </div>

                <!-- Profile Icon (Logged In) -->
                <div class="hidden sm:block">
                    <a href="/profile" class="p-2 text-gray-600 hover:text-secondary transition-colors">
                        <i class="fas fa-user w-5 h-5"></i>
                    </a>
                </div>

                <!-- Wishlist Icon -->
                <a href="/snapshop/wishlist" class="relative p-2 text-gray-600 hover:text-red-500 transition-colors">
                    <i class="fas fa-heart w-5 h-5"></i>
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                        0
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
                
                <!-- Mobile Sign In/Sign Up -->
                <div class="pt-3 space-y-2">
                    <button class="block w-full text-left text-gray-600 hover:text-secondary relative group">
                        Sign In
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-secondary group-hover:w-full transition-all duration-300"></span>
                    </button>
                    <button class="block w-full text-left text-gray-600 hover:text-secondary relative group">
                        Sign Up
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-secondary group-hover:w-full transition-all duration-300"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Simple JavaScript for mobile menu toggle -->
<script>
document.getElementById('mobileMenuBtn').addEventListener('click', function() {
    const mobileMenu = document.getElementById('mobileMenu');
    mobileMenu.classList.toggle('hidden');
});
</script>
