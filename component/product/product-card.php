<?php
// ProductCard Component - Converted from React
// This component displays a single product card with all the same styling and functionality

require_once __DIR__ . '/../../utils/currency.php';
require_once __DIR__ . '/../../auth/auth-helper.php';
startSessionIfNotStarted();

$product = $GLOBALS['currentProduct'] ?? null;

$uniqueId = 'product_' . uniqid();

$price = isset($product['price']) ? $product['price'] : 0;
$highPrice = isset($product['highPrice']) ? $product['highPrice'] : 0;

$formattedPrice = getINRSymbol() . $price;
$formattedHighPrice = getINRSymbol() . ($highPrice > 0 ? $highPrice : $price);

$productId = $product['id'] ?? $product['_id'] ?? '';
?>

<div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow" id="<?php echo $uniqueId; ?>">
    <div class="relative">
        <img src="<?php echo $product['image'] ?? 'https://via.placeholder.com/300x200?text=Product+Image'; ?>" 
             alt="<?php echo $product['name'] ?? 'Product Image'; ?>" 
             class="w-full h-48 object-cover rounded-t-lg cursor-pointer hover:opacity-90 transition-opacity"
             onclick="handleTitleClick_<?php echo $uniqueId; ?>()"
             title="Click to view details" />

        <?php if (isset($product['discount']) && $product['discount'] > 0): ?>
            <span class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                <?php echo $product['discount']; ?>% OFF
            </span>
        <?php endif; ?>
    </div>

    <div class="p-4">
        <h3 class="font-semibold text-gray-900 mb-2 truncate cursor-pointer hover:text-blue-600 transition-colors"
            onclick="handleTitleClick_<?php echo $uniqueId; ?>()"
            title="Click to view details">
            <?php echo $product['name'] ?? 'Product Name'; ?>
        </h3>

        <div class="flex items-center justify-between mb-3">
            <div class="flex flex-col">
                <span class="text-lg font-bold text-blue-600">
                    <?php echo $formattedPrice; ?>
                </span>
                <?php if (isset($product['discount']) && $product['discount'] > 0): ?>
                    <span class="text-sm text-gray-400 line-through">
                        <?php echo $formattedHighPrice; ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="text-right">
                <span class="text-sm text-gray-500 capitalize block">
                    <?php echo $product['category'] ?? 'Category'; ?>
                </span>
                <span class="text-xs text-gray-400 capitalize">
                    <?php echo $product['gender'] ?? 'Gender'; ?>
                </span>
            </div>
        </div>

        <!-- Add to Cart Button - Same UI as product.php -->
        <div class="flex gap-2">
            <button onclick="handleAddToCart_<?php echo $uniqueId; ?>()"
                    class="flex-1 py-3 px-6 text-white rounded-xl font-semibold text-base flex items-center justify-center gap-2 bg-blue-600 shadow-lg hover:bg-blue-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                Add to Cart
            </button>
        </div>
    </div>
</div>

<script>
        // Add to Cart functionality
        function handleAddToCart_<?php echo $uniqueId; ?>() {
            const productId = '<?php echo $productId; ?>';
            const isLoggedIn = <?php echo isUserLoggedIn() ? 'true' : 'false'; ?>;
            
            if (productId) {
                if (isLoggedIn) {
                    // Add product to cart via API
                    fetch('/snapshop/api/cart-add.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ 
                            product_id: productId,
                            quantity: 1
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (typeof showToast === 'function') {
                                showToast('Product added to cart successfully!', 'success', 3000);
                            } else {
                                alert('Product added to cart successfully!');
                            }
                            
                            // Update header cart count
                            updateHeaderCartCount(data.total_items);
                            
                            // Show success message only, don't open cart sidebar
                        } else {
                            if (typeof showToast === 'function') {
                                showToast(data.message || 'Failed to add to cart', 'error', 3000);
                            } else {
                                alert(data.message || 'Failed to add to cart');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Cart error:', error);
                        if (typeof showToast === 'function') {
                            showToast('Failed to add to cart', 'error', 3000);
                        } else {
                            alert('Failed to add to cart');
                        }
                    });
                } else {
                    // Show warning toast for non-logged-in users
                    if (typeof showToast === 'function') {
                        showToast('Please login to add items to cart', 'warning', 4000);
                    } else {
                        alert('Please login to add items to cart');
                    }
                    
                    // Open login modal after a short delay
                    setTimeout(() => {
                        if (typeof openLoginModal === 'function') {
                            openLoginModal();
                        }
                    }, 1000);
                }
            }
        }

        // Toggle Wishlist functionality
        function handleToggleWishlist_<?php echo $uniqueId; ?>() {
            const productId = '<?php echo $productId; ?>';
            const isLoggedIn = <?php echo isUserLoggedIn() ? 'true' : 'false'; ?>;
            
            if (isLoggedIn) {
                // Toggle wishlist in database
                fetch('/snapshop/api/wishlist-toggle.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        product_id: productId,
                        action: 'toggle'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const message = data.in_wishlist ? 'Product added to wishlist!' : 'Product removed from wishlist';
                        
                        if (typeof showToast === 'function') {
                            showToast(message, 'success', 3000);
                        } else {
                            alert(message);
                        }
                        
                        // Reload page to update wishlist status
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Failed to update wishlist', 'error', 3000);
                        } else {
                            alert(data.message || 'Failed to update wishlist');
                        }
                    }
                })
                .catch(error => {
                    console.error('Wishlist error:', error);
                    if (typeof showToast === 'function') {
                        showToast('Failed to update wishlist', 'error', 3000);
                    } else {
                        alert('Failed to update wishlist');
                    }
                });
            } else {
                // Show warning toast for non-logged-in users
                if (typeof showToast === 'function') {
                    showToast('Please login to manage wishlist', 'warning', 4000);
                } else {
                    alert('Please login to manage wishlist');
                }
                
                // Open login modal after a short delay
                setTimeout(() => {
                    if (typeof openLoginModal === 'function') {
                        openLoginModal();
                    }
                }, 1000);
            }
        }

function handleLoginRequired_<?php echo $uniqueId; ?>() {
    // Check if toast function exists
    if (typeof showToast === 'function') {
        showToast('Please login to add items to cart', 'warning', 4000);
    } else {
        alert('Please login to add items to cart');
    }
    
    // Open login modal after a short delay
    setTimeout(() => {
        if (typeof openLoginModal === 'function') {
            openLoginModal();
        }
    }, 1000);
}

function handleTitleClick_<?php echo $uniqueId; ?>() {
    const productId = '<?php echo $productId; ?>';
    if (productId) {
        // Navigate to product detail page using simple direct link
        window.location.href = '/snapshop/product.php?id=' + productId;
    }
}
</script>

    <!-- Function to update header cart count -->
    <script>
        function updateHeaderCartCount(count) {
            const headerCartCount = document.querySelector('.header-cart-count');
            if (headerCartCount) {
                headerCartCount.textContent = count;
            }
        }
    </script>
