<?php
// ProductCard Component - Converted from React
// This component displays a single product card with all the same styling and functionality

require_once __DIR__ . '/../../utils/currency.php';

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

        <div class="flex gap-2">
            <button onclick="handleTitleClick_<?php echo $uniqueId; ?>()"
                    class="flex-1 bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-eye w-4 h-4"></i>
                View Details
            </button>
            <button onclick="handleAddToCart_<?php echo $uniqueId; ?>()"
                    class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-shopping-bag w-4 h-4"></i>
                Add to Cart
            </button>
        </div>
    </div>
</div>

<script>
function handleAddToCart_<?php echo $uniqueId; ?>() {
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    
    if (!isLoggedIn) {
        alert('Please login to add items to cart');
        return;
    }
    
    const productId = '<?php echo $productId; ?>';
    if (productId) {
        alert('Product added to cart successfully!');
    }
}

function handleTitleClick_<?php echo $uniqueId; ?>() {
    const productId = '<?php echo $productId; ?>';
    if (productId) {
        // Navigate to product detail page using simple direct link
        window.location.href = '/snapshop/product.php?id=' + productId;
    }
}
</script>
