<?php
// TrendingProducts Component
// This component displays trending products in a grid layout

require_once __DIR__ . '/../ui/button.php';
require_once __DIR__ . '/../ui/loading-spinner.php';
require_once __DIR__ . '/../ui/error-state.php';

$trending_products = [];
$trending_loading = false;
$trending_error = null;

try {
    $database_path = __DIR__ . '/../../config/database.php';
    $product_model_path = __DIR__ . '/../../modal/product.model.php';
    
    require_once $database_path;
    require_once $product_model_path;
    
    $conn = setupDatabase();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    $productModel = new Product($conn);
    
    $trending_products = $productModel->getTrendingProducts(18);
    
    $trending_loading = false;
    
} catch (Exception $e) {
    $trending_error = $e->getMessage();
    $trending_loading = false;
    $trending_products = [];
} catch (Error $e) {
    $trending_error = "Fatal error: " . $e->getMessage();
    $trending_loading = false;
    $trending_products = [];
}

function getTrendingProducts($products) {
    if (!is_array($products)) {
        return [];
    }
    return array_slice($products, 0, 18);
}

$trending_products_list = getTrendingProducts($trending_products);
?>

<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-3xl font-bold text-primary mb-3 hover:text-accent transition-colors duration-300 cursor-default">
                Trending Products
            </h2>
            <p class="text-neutral max-w-lg mx-auto hover:text-primary transition-colors duration-300">
                Discover what's popular and trending right now
            </p>
        </div>

        <?php if ($trending_loading): ?>
            <div class="text-center py-12">
                <?php echo renderLoadingSpinner(['size' => 'md', 'variant' => 'primary', 'text' => 'Loading...']); ?>
            </div>
        <?php elseif ($trending_error): ?>
            <?php echo renderErrorState(['error' => $trending_error, 'onRetry' => 'window.location.reload()']); ?>
        <?php elseif (!empty($trending_products_list)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-10">
                <?php foreach ($trending_products_list as $index => $trending_product): ?>
                    <div class="group transform hover:scale-105 hover:-translate-y-1 transition-all duration-300 ease-in-out"
                         data-aos="fade-up"
                         data-aos-delay="<?php echo $index * 100; ?>">
                        
                        <?php 
                        $GLOBALS['currentProduct'] = $trending_product;
                        include 'component/product/product-card.php';
                        ?>
                        
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <p class="text-neutral mb-4">
                    <?php echo empty($trending_products) 
                        ? 'No products available at the moment.'
                        : 'No trending products found.'; ?>
                </p>
                <?php if (empty($trending_products)): ?>
                    <a href="/snapshop/products" class="text-accent hover:text-accent/80 font-medium">
                        Browse All Products
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!$trending_loading && !$trending_error && !empty($trending_products_list)): ?>
            <div class="text-center" data-aos="fade-up" data-aos-delay="400">
                <?php echo renderButton([
                    'variant' => 'primary',
                    'size' => 'lg',
                    'children' => 'View All Products',
                    'onClick' => 'window.location.href="/snapshop/products"'
                ]); ?>
            </div>
        <?php endif; ?>
    </div>
</section>
