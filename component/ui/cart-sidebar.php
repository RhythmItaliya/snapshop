<?php
// Cart Sidebar Component - Shows cart items when cart icon is clicked

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!-- Cart Sidebar Overlay -->
<div id="cartSidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden" onclick="closeCartSidebar()"></div>

<!-- Cart Sidebar -->
<div id="cartSidebar" class="fixed right-0 top-0 h-full w-80 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out z-50">
    <!-- Cart Header -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-900">Shopping Cart</h2>
        <div class="flex items-center space-x-2">
            <button id="clearCartBtn" onclick="handleClearCart()" 
                    class="bg-red-600 text-white px-3 py-1 rounded-lg hover:bg-red-700 transition-colors text-sm hidden">
                Clear Cart
            </button>
            <button onclick="closeCartSidebar()" class="text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-times w-5 h-5"></i>
            </button>
        </div>
    </div>

    <!-- Cart Content -->
    <div class="flex-1 overflow-y-auto p-4">
        <?php if (!$isLoggedIn): ?>
            <!-- Not Logged In State -->
            <div class="text-center py-12">
                <div class="text-gray-400 text-4xl mb-4">🛒</div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Sign in to view your cart</h3>
                <p class="text-gray-600 mb-6">Please login to see your shopping cart items</p>
                <div class="flex flex-col gap-3">
                    <button onclick="openLoginModal()" 
                            class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                        Sign In
                    </button>
                    <button onclick="openRegisterModal()" 
                            class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                        Sign Up
                    </button>
                </div>
            </div>
        <?php else: ?>
            <!-- Cart Items Container -->
            <div id="cartItemsContainer">
                <!-- Loading State -->
                <div id="cartLoading" class="text-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-2 text-gray-600">Loading cart...</p>
                </div>
                
                <!-- Empty Cart State -->
                <div id="emptyCart" class="text-center py-8 hidden">
                    <!-- Empty Cart Item Template (Same UI as regular items) -->
                    <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-xl bg-white opacity-50">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shopping-bag text-gray-400 text-xl"></i>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-medium text-gray-400 truncate">Your cart is empty</h3>
                            <p class="text-sm text-gray-400">Add some products to get started!</p>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 border border-gray-200 rounded bg-gray-50 flex items-center justify-center">
                                    <span class="text-gray-400 text-xs">-</span>
                                </div>
                                <span class="text-sm font-medium text-gray-400 min-w-[2rem] text-center">0</span>
                                <div class="w-8 h-8 border border-gray-200 rounded bg-gray-50 flex items-center justify-center">
                                    <span class="text-gray-400 text-xs">+</span>
                                </div>
                            </div>

                            <div class="text-right min-w-[4rem]">
                                <p class="text-sm font-semibold text-gray-400">₹0.00</p>
                            </div>

                            <div class="w-8 h-8 bg-gray-50 rounded flex items-center justify-center ml-2">
                                <i class="fas fa-times text-gray-400 text-xs"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Continue Shopping Button -->
                    <div class="mt-6">
                        <button onclick="closeCartSidebar()" 
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                            Continue Shopping
                        </button>
                    </div>
                </div>
                
                <!-- Cart Items List -->
                <div id="cartItemsList" class="space-y-3 hidden">
                    <!-- Cart items will be dynamically loaded here -->
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Cart Footer -->
    <?php if ($isLoggedIn): ?>
        <div class="border-t border-gray-200 p-4">
            <!-- Cart Summary -->
            <div class="flex justify-between items-center mb-3">
                <span class="text-base font-semibold text-gray-900">Total:</span>
                <span id="cartTotal" class="text-lg font-bold text-blue-600">₹0.00</span>
            </div>
            
            <!-- Checkout Button -->
            <button id="checkoutBtn" onclick="handleCheckout()" 
                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                Proceed to Checkout
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Cart Item Template (Hidden) -->
<template id="cartItemTemplate">
    <div class="cart-item flex items-center space-x-4 p-4 border border-gray-200 rounded-xl hover:shadow-md transition-shadow bg-white" data-product-id="">
        <div class="flex-shrink-0">
            <img src="" alt="" class="w-16 h-16 object-cover rounded-lg product-image" />
        </div>

        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-medium text-gray-900 truncate product-name mb-1"></h3>
            <p class="text-sm text-gray-600 product-price"></p>
        </div>

        <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-2">
                <button onclick="updateCartItemQuantity(this, -1)" 
                        class="quantity-btn w-8 h-8 p-0 border border-gray-300 text-gray-600 hover:bg-gray-100 rounded transition-colors flex items-center justify-center">
                    <i class="fas fa-minus text-xs"></i>
                </button>

                <span class="text-sm font-medium text-gray-900 min-w-[2rem] text-center quantity-display">1</span>

                <button onclick="updateCartItemQuantity(this, 1)" 
                        class="quantity-btn w-8 h-8 p-0 border border-gray-300 text-gray-600 hover:bg-gray-100 rounded transition-colors flex items-center justify-center">
                    <i class="fas fa-plus text-xs"></i>
                </button>
            </div>

            <div class="text-right min-w-[4rem]">
                <p class="text-sm font-semibold text-gray-900 item-total"></p>
            </div>

            <button onclick="removeCartItem(this)" 
                    class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded transition-colors ml-2" 
                    title="Remove item">
                <i class="fas fa-times w-4 h-4"></i>
            </button>
        </div>
    </div>
</template>

<script>
// Cart Sidebar Functions
function openCartSidebar() {
    document.getElementById('cartSidebarOverlay').classList.remove('hidden');
    document.getElementById('cartSidebar').classList.remove('translate-x-full');
    
    // Load cart items if user is logged in
    if (<?php echo $isLoggedIn ? 'true' : 'false'; ?>) {
        loadCartItems();
    }
}

function closeCartSidebar() {
    document.getElementById('cartSidebarOverlay').classList.add('hidden');
    document.getElementById('cartSidebar').classList.add('translate-x-full');
}

// Load cart items from server
function loadCartItems() {
    const loadingEl = document.getElementById('cartLoading');
    const emptyEl = document.getElementById('emptyCart');
    const itemsEl = document.getElementById('cartItemsList');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const container = document.getElementById('cartItemsContainer');
    
    // Show loading
    loadingEl.classList.remove('hidden');
    emptyEl.classList.add('hidden');
    itemsEl.classList.add('hidden');
    
    // Fetch cart items
    fetch('/snapshop/api/cart-items.php')
        .then(response => response.json())
        .then(data => {
            loadingEl.classList.add('hidden');
            
            if (data.success && data.items && data.items.length > 0) {
                // Show items and clear cart button
                displayCartItems(data.items);
                updateCartTotal(data.total);
                itemsEl.classList.remove('hidden');
                clearCartBtn.classList.remove('hidden');
            } else {
                // Show empty state and hide clear cart button
                emptyEl.classList.remove('hidden');
                clearCartBtn.classList.add('hidden');
                updateCartTotal(0);
            }
        })
        .catch(error => {
            console.error('Error loading cart:', error);
            loadingEl.classList.add('hidden');
            emptyEl.classList.remove('hidden');
            clearCartBtn.classList.add('hidden');
            updateCartTotal(0);
        });
}

// Display cart items
function displayCartItems(items) {
    const container = document.getElementById('cartItemsList');
    const template = document.getElementById('cartItemTemplate');
    
    container.innerHTML = '';
    
    items.forEach(item => {
        const itemEl = template.content.cloneNode(true);
        const cartItem = itemEl.querySelector('.cart-item');
        
        // Set data attributes
        cartItem.dataset.productId = item.product_id;
        cartItem.dataset.quantity = item.quantity;
        
        // Set content
        cartItem.querySelector('.product-image').src = item.product.image || '/placeholder-product.jpg';
        cartItem.querySelector('.product-image').alt = item.product.name;
        cartItem.querySelector('.product-name').textContent = item.product.name;
        cartItem.querySelector('.product-price').textContent = '₹' + parseFloat(item.price).toFixed(2);
        cartItem.querySelector('.quantity-display').textContent = item.quantity;
        cartItem.querySelector('.item-total').textContent = '₹' + (parseFloat(item.price) * item.quantity).toFixed(2);
        
        container.appendChild(itemEl);
    });
}

// Update cart item quantity
function updateCartItemQuantity(button, change) {
    const cartItem = button.closest('.cart-item');
    const productId = cartItem.dataset.productId;
    const currentQuantity = parseInt(cartItem.dataset.quantity);
    const newQuantity = currentQuantity + change;
    
    if (newQuantity > 0) {
        // Update quantity in database
        fetch('/snapshop/api/cart-update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                product_id: productId,
                quantity: newQuantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update local display
                cartItem.dataset.quantity = newQuantity;
                cartItem.querySelector('.quantity-display').textContent = newQuantity;
                cartItem.querySelector('.item-total').textContent = '₹' + (parseFloat(data.price) * newQuantity).toFixed(2);
                
                // Update total
                updateCartTotal(data.total);
                
                // Update header cart count
                updateHeaderCartCount(data.total_items);
            } else {
                if (typeof showToast === 'function') {
                    showToast(data.message || 'Failed to update quantity', 'error', 3000);
                }
            }
        })
        .catch(error => {
            console.error('Error updating quantity:', error);
            if (typeof showToast === 'function') {
                showToast('Failed to update quantity', 'error', 3000);
            }
        });
    }
}

// Remove cart item
function removeCartItem(button) {
    const cartItem = button.closest('.cart-item');
    const productId = cartItem.dataset.productId;
    
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        fetch('/snapshop/api/cart-remove.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove item from display
                cartItem.remove();
                
                // Update total
                updateCartTotal(data.total);
                
                // Update header cart count
                updateHeaderCartCount(data.total_items);
                
                // Show empty state if no items left
                const remainingItems = document.querySelectorAll('.cart-item');
                if (remainingItems.length === 0) {
                    document.getElementById('cartItemsList').classList.add('hidden');
                    document.getElementById('emptyCart').classList.remove('hidden');
                    document.getElementById('clearCartBtn').classList.add('hidden');
                }
                
                if (typeof showToast === 'function') {
                    showToast('Item removed from cart', 'success', 3000);
                }
            } else {
                if (typeof showToast === 'function') {
                    showToast(data.message || 'Failed to remove item', 'error', 3000);
                }
            }
        })
        .catch(error => {
            console.error('Error removing item:', error);
            if (typeof showToast === 'function') {
                showToast('Failed to remove item', 'error', 3000);
            }
        });
    }
}

// Update cart total
function updateCartTotal(total) {
    document.getElementById('cartTotal').textContent = '₹' + parseFloat(total).toFixed(2);
}

// Update header cart count
function updateHeaderCartCount(count) {
    const headerCartCount = document.querySelector('.header-cart-count');
    if (headerCartCount) {
        headerCartCount.textContent = count;
    }
}

// Handle checkout
function handleCheckout() {
    // Redirect to checkout page or show checkout modal
    if (typeof showToast === 'function') {
        showToast('Checkout functionality coming soon!', 'info', 3000);
    }
}

// Handle clear cart
function handleClearCart() {
    if (confirm('Are you sure you want to clear your cart? This action cannot be undone.')) {
        fetch('/snapshop/api/cart-clear.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear local display
                document.getElementById('cartItemsList').innerHTML = '';
                document.getElementById('emptyCart').classList.remove('hidden');
                updateCartTotal(0);
                updateHeaderCartCount(0);
                document.getElementById('clearCartBtn').classList.add('hidden'); // Hide clear cart button after clearing
                if (typeof showToast === 'function') {
                    showToast('Cart cleared successfully!', 'success', 3000);
                }
            } else {
                if (typeof showToast === 'function') {
                    showToast(data.message || 'Failed to clear cart', 'error', 3000);
                }
            }
        })
        .catch(error => {
            console.error('Error clearing cart:', error);
            if (typeof showToast === 'function') {
                showToast('Failed to clear cart', 'error', 3000);
            }
        });
    }
}

// Close sidebar when pressing Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeCartSidebar();
    }
});
</script>
