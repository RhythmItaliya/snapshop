<?php
// ProductDetails Component - Converted from React
// This component displays detailed product information with the same UI design

// Include currency utility
require_once __DIR__ . '/../../utils/currency.php';

// Get product ID from URL parameter
$productId = $_GET['id'] ?? null;

// Initialize variables
$product = null;
$loading = true;
$error = null;
$isInWishlist = false;

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

try {
    if (!$productId) {
        throw new Exception('Product ID is required');
    }

    // Get database connection
    $database_path = __DIR__ . '/../../config/database.php';
    $product_model_path = __DIR__ . '/../../modal/product.model.php';
    
    require_once $database_path;
    require_once $product_model_path;
    
    // Create database connection
    $conn = getDatabaseConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Initialize Product model
    $productModel = new Product($conn);
    
    // Fetch product details
    $product = $productModel->getProductById($productId);
    
    if (!$product) {
        throw new Exception('Product not found');
    }
    
    $loading = false;
    
} catch (Exception $e) {
    $error = $e->getMessage();
    $loading = false;
    $product = null;
} catch (Error $e) {
    $error = "Fatal error: " . $e->getMessage();
    $loading = false;
    $product = null;
}

// Check wishlist status if user is logged in
if ($isLoggedIn && $product) {
    try {
        $wishlist_model_path = __DIR__ . '/../../modal/wishlist.model.php';
        require_once $wishlist_model_path;
        
        // Create a new connection for wishlist check
        $wishlistConn = getDatabaseConnection();
        if ($wishlistConn) {
            $wishlistModel = new Wishlist($wishlistConn);
            $isInWishlist = $wishlistModel->isProductInWishlist($_SESSION['user_id'], $productId);
            $wishlistConn->close();
        } else {
            $isInWishlist = false;
        }
    } catch (Exception $e) {
        // Silent fail for wishlist check
        $isInWishlist = false;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_to_cart') {
            if (!$isLoggedIn) {
                $error = 'Please login to add items to cart';
            } else {
                // Add to cart logic here
                $success = 'Product added to cart successfully!';
            }
        } elseif ($_POST['action'] === 'toggle_wishlist') {
            if (!$isLoggedIn) {
                $error = 'Please login to manage wishlist';
            } else {
                // Toggle wishlist logic here
                $isInWishlist = !$isInWishlist;
                $success = $isInWishlist ? 'Product added to wishlist successfully!' : 'Product removed from wishlist';
            }
        }
    }
}

// Extract product data
if ($product) {
    $name = $product['name'] ?? 'Product Name';
    $price = $product['price'] ?? 0;
    $highPrice = $product['highPrice'] ?? 0;
    $description = $product['description'] ?? 'No description available';
    $image = $product['image'] ?? 'https://via.placeholder.com/400x400?text=Product+Image';
    $discount = $product['discount'] ?? 0;
    $stock = $product['stock'] ?? 0;
    $category = $product['category'] ?? 'Category';
    $sizes = $product['sizes'] ?? [];
    $colors = $product['colors'] ?? [];
    $gender = $product['gender'] ?? 'Unisex';
}

// Format prices
$formattedPrice = getINRSymbol() . $price;
$formattedHighPrice = getINRSymbol() . $highPrice;
?>

<?php if ($loading): ?>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center">
        <div class="text-center">
            <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-600 text-lg">Loading product...</p>
        </div>
    </div>
<?php elseif ($error): ?>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center">
        <div class="text-center">
            <div class="text-red-500 text-6xl mb-4">⚠️</div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Error</h2>
            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($error); ?></p>
            <button onclick="window.location.reload()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Try Again
            </button>
        </div>
    </div>
<?php elseif (!$product): ?>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center">
        <div class="text-center">
            <div class="text-gray-400 text-6xl mb-4">🔍</div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Product Not Found</h2>
            <p class="text-gray-600 mb-4">The product you're looking for doesn't exist.</p>
            <button onclick="window.location.reload()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Try Again
            </button>
        </div>
    </div>
<?php else: ?>
    <section class="min-h-screen bg-gray-50 py-8">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Breadcrumb Navigation -->
            <div class="mb-8">
                <nav class="flex items-center justify-between" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="/snapshop/" class="text-gray-700 hover:text-blue-600 transition-colors">
                                Home
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                                        <a href="/snapshop/products.php" class="ml-1 text-gray-700 hover:text-blue-600 transition-colors md:ml-2">
                            Products
                        </a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-700 md:ml-2"><?php echo htmlspecialchars($category); ?></span>
                            </div>
                        </li>
                    </ol>
                    
                    <!-- Back to Products Button -->
                    <a href="/snapshop/products.php" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Products
                    </a>
                </nav>
            </div>

            <!-- Success/Error Messages -->
            <?php if (isset($success)): ?>
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Product Details Card -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                    <!-- Product Image -->
                    <div class="bg-gray-100 p-8 flex items-center justify-center">
                        <div class="relative group">
                            <img class="w-96 h-96 object-cover rounded-xl shadow-lg transition-transform duration-300 group-hover:scale-105"
                                 src="<?php echo htmlspecialchars($image); ?>"
                                 alt="<?php echo htmlspecialchars($name); ?>" />
                            <?php if ($discount > 0): ?>
                                <div class="absolute -top-4 -right-4 bg-red-500 text-white text-sm font-bold px-3 py-1 rounded-full shadow-lg">
                                    -<?php echo $discount; ?>%
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Product Information -->
                    <div class="p-8 lg:p-12">
                        <!-- Category Badge -->
                        <div class="mb-4">
                            <span class="inline-block bg-blue-100 text-blue-600 text-sm font-medium px-3 py-1 rounded-full">
                                <?php echo htmlspecialchars($category); ?>
                            </span>
                        </div>

                        <!-- Product Name -->
                        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-6 leading-tight">
                            <?php echo htmlspecialchars($name); ?>
                        </h1>

                        <!-- Pricing -->
                        <div class="mb-8">
                            <?php if ($discount > 0): ?>
                                <div class="flex items-center gap-4">
                                    <span class="text-4xl font-bold text-red-500">
                                        <?php echo $formattedPrice; ?>
                                    </span>
                                    <span class="text-2xl text-gray-400 line-through">
                                        <?php echo $formattedHighPrice; ?>
                                    </span>
                                    <span class="bg-red-100 text-red-600 text-sm font-semibold px-3 py-1 rounded-full">
                                        <?php echo $discount; ?>% OFF
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="text-4xl font-bold text-red-500"><?php echo $formattedPrice; ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Stock Status -->
                        <div class="mb-6">
                            <?php
                            $stockStatus = $stock > 10 
                                ? ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'dot' => 'bg-green-500', 'label' => 'In Stock']
                                : ($stock > 0 
                                    ? ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'dot' => 'bg-yellow-500', 'label' => "Only {$stock} left"]
                                    : ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'dot' => 'bg-red-500', 'label' => 'Out of Stock']
                                );
                            ?>
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium <?php echo $stockStatus['bg']; ?> <?php echo $stockStatus['text']; ?>">
                                <span class="w-2 h-2 rounded-full mr-2 <?php echo $stockStatus['dot']; ?>"></span>
                                <?php echo $stockStatus['label']; ?>
                            </span>
                        </div>

                        <!-- Description -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Description</h3>
                            <p class="text-gray-600 leading-relaxed text-base"><?php echo htmlspecialchars($description); ?></p>
                        </div>

                        <!-- Product Details -->
                        <div class="mb-8 space-y-4">
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-medium text-gray-500">Gender:</span>
                                <span class="text-sm font-medium text-gray-900 capitalize"><?php echo htmlspecialchars($gender); ?></span>
                            </div>

                            <?php if (!empty($sizes)): ?>
                                <div class="flex items-center gap-4">
                                    <span class="text-sm font-medium text-gray-500">Available Sizes:</span>
                                    <div class="flex gap-2">
                                        <?php foreach ($sizes as $size): ?>
                                            <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">
                                                <?php echo htmlspecialchars($size); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($colors)): ?>
                                <div class="flex items-center gap-4">
                                    <span class="text-sm font-medium text-gray-500">Available Colors:</span>
                                    <div class="flex gap-2">
                                        <?php foreach ($colors as $color): ?>
                                            <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">
                                                <?php echo htmlspecialchars($color); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Buttons -->
                        <form method="POST" class="flex flex-col sm:flex-row gap-4">
                            <button type="submit" name="action" value="add_to_cart"
                                    <?php echo $stock === 0 ? 'disabled' : ''; ?>
                                    class="flex-1 py-4 px-8 text-white rounded-xl font-semibold text-lg flex items-center justify-center gap-2 <?php echo $stock > 0 ? 'bg-blue-600 shadow-lg hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'; ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                <?php echo $stock > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                            </button>

                            <button type="submit" name="action" value="toggle_wishlist"
                                    class="py-4 px-6 border-2 rounded-xl font-semibold text-lg flex items-center justify-center gap-2 <?php echo $isInWishlist ? 'text-red-500 border-red-500 bg-red-50' : 'text-green-600 border-green-600 bg-green-50'; ?>">
                                <svg class="w-5 h-5 <?php echo $isInWishlist ? 'fill-current' : ''; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                <?php echo $isInWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>
                            </button>
                        </form>

                        <!-- Stock Information -->
                        <div class="mt-8 pt-8 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Stock Available</h4>
                                    <p class="text-gray-900 font-medium text-lg"><?php echo $stock; ?> units</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
