<?php
// NewCollection Component - Converted from React
// This component displays the latest products in a slick carousel slider

// Get database connection
$conn = getDatabaseConnection();

// Initialize variables
$products = [];
$loading = false;
$error = null;

// Fetch new products from database
if ($conn) {
    try {
        $loading = true;
        
        // Get the latest 10 products sorted by creation date
        $sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 10";
        $result = $conn->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        } else {
            throw new Exception("Failed to fetch products");
        }
        
        $loading = false;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        $loading = false;
    }
} else {
    $error = "Database connection failed";
}

// Get new products count
$newProductsCount = count($products);

// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>

<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">New Collection</h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                Discover our latest arrivals and newest additions to our collection
            </p>
        </div>

        <?php if ($loading): ?>
            <div class="text-center py-12">
                <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-4 text-gray-600 text-lg">Loading new products...</p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="text-center py-12">
                <div class="text-red-500 text-6xl mb-4">⚠️</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Error</h3>
                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($error); ?></p>
                <button onclick="window.location.reload()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Try Again
                </button>
            </div>
        <?php endif; ?>

        <?php if (!$loading && !$error && $newProductsCount > 0): ?>
            <div class="relative mb-8" data-aos="fade-up" data-aos-delay="200">
                <!-- Slick Carousel Container -->
                <div class="new-collection-slider">
                    <?php foreach ($products as $product): ?>
                        <div class="px-3">
                            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                                <?php 
                                // Set the current product for the ProductCard component
                                $GLOBALS['currentProduct'] = $product;
                                ?>
                                <?php include __DIR__ . '/../product/product-card.php'; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Simple Navigation Buttons Below Slider -->
                <div class="flex justify-center items-center gap-4 mt-6">
                    <button type="button" class="prev-btn bg-white p-3 rounded-lg shadow-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button type="button" class="next-btn bg-white p-3 rounded-lg shadow-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$loading && !$error && $newProductsCount === 0): ?>
            <div class="text-center py-12">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No New Products Available</h3>
                <p class="text-gray-600 mb-4">
                    We're currently setting up our product catalog. Check back soon!
                </p>
                <a href="/snapshop/products.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    Browse All Products
                </a>
            </div>
        <?php endif; ?>

        <?php if (!$loading && !$error && $newProductsCount > 0): ?>
            <div class="text-center" data-aos="fade-up" data-aos-delay="400">
                <a href="/snapshop/products.php" class="inline-block bg-blue-600 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-700 transition-colors">
                    View All Products
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Slick Carousel CSS -->
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>

<!-- Slick Carousel JavaScript -->
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize New Collection Slider
    $('.new-collection-slider').slick({
        dots: false,
        infinite: true,
        speed: 500,
        slidesToShow: 4,
        slidesToScroll: 1,
        autoplay: false,
        pauseOnHover: true,
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                },
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1,
                },
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                },
            },
        ],
        arrows: false,
        cssEase: 'ease',
        swipeToSlide: true,
        lazyLoad: false,
        adaptiveHeight: false,
        centerMode: false,
        focusOnSelect: false,
        accessibility: true,
    });
    
    // Custom navigation buttons
    $('.prev-btn').on('click', function() {
        $('.new-collection-slider').slick('slickPrev');
    });
    
    $('.next-btn').on('click', function() {
        $('.new-collection-slider').slick('slickNext');
    });
});
</script>

<style>
/* Custom Slick Carousel Styles */
.new-collection-slider .slick-slide {
    padding: 0 10px;
}
</style>
