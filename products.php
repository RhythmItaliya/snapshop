<?php
// Products Page - Converted from React
// This page displays all products with filtering and sorting capabilities

// Start session for user authentication
session_start();

// Include all necessary utilities and components
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/component/ui/loading-spinner.php';
require_once __DIR__ . '/component/ui/error-state.php';

// Get database connection
$conn = getDatabaseConnection();

// Initialize variables
$products = [];
$loading = false;
$error = null;

// Get filter parameters from URL (will be handled by filter-sidebar.php)
$selectedCategory = $_GET['category'] ?? 'all';
$priceRange = [
    $_GET['min_price'] ?? 0,
    $_GET['max_price'] ?? 1000
];
$sortBy = $_GET['sort'] ?? 'featured';

// Fetch products from database
if ($conn) {
    try {
        $loading = true;
        
        // Build the SQL query based on filters
        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];
        $types = "";
        
        // Add category filter
        if ($selectedCategory !== 'all') {
            if ($selectedCategory === 'men') {
                $sql .= " AND LOWER(gender) = ?";
                $params[] = 'men';
                $types .= "s";
            } elseif ($selectedCategory === 'women') {
                $sql .= " AND LOWER(gender) = ?";
                $params[] = 'women';
                $types .= "s";
            } elseif ($selectedCategory === 'unisex') {
                $sql .= " AND LOWER(gender) = ?";
                $params[] = 'unisex';
                $types .= "s";
            } elseif ($selectedCategory === 'sale') {
                $sql .= " AND discount > 0";
            } else {
                $sql .= " AND LOWER(category) = ?";
                $params[] = strtolower($selectedCategory);
                $types .= "s";
            }
        }
        
        // Add price range filter
        $sql .= " AND price >= ? AND price <= ?";
        $params[] = $priceRange[0];
        $params[] = $priceRange[1];
        $types .= "dd";
        
        // Add sorting
        switch ($sortBy) {
            case 'price_low_to_high':
                $sql .= " ORDER BY price ASC";
                break;
            case 'price_high_to_low':
                $sql .= " ORDER BY price DESC";
                break;
            case 'newest':
                $sql .= " ORDER BY created_at DESC";
                break;
            case 'name_a_to_z':
                $sql .= " ORDER BY name ASC";
                break;
            default: // featured - keep original order
                $sql .= " ORDER BY id ASC";
                break;
        }
        
        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            
            $stmt->close();
        } else {
            throw new Exception("Failed to prepare statement");
        }
        
        $loading = false;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        $loading = false;
    }
} else {
    $error = "Database connection failed";
}

// Get filtered products count
$filteredProductsCount = count($products);

// Get total products count (for comparison)
$totalProductsCount = 0;
if ($conn) {
    try {
        $result = $conn->query("SELECT COUNT(*) as total FROM products");
        if ($result) {
            $row = $result->fetch_assoc();
            $totalProductsCount = $row['total'];
        }
    } catch (Exception $e) {
        // Ignore error for total count
    }
}

// Close database connection
if ($conn) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - SnapShop</title>
    <link href="assets/css/tailwind.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Include Header -->
    <?php include 'component/header.php'; ?>
    
    <div class="pt-20">
        <!-- Hero Section -->
        <section class="bg-white py-8">
            <div class="container mx-auto px-4">
                <div class="text-center">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">
                        <?php 
                        if ($selectedCategory === 'all') {
                            echo 'Products';
                        } else {
                            echo ucfirst($selectedCategory);
                        }
                        ?>
                    </h1>
                    <div class="text-sm text-gray-500">
                        Showing <?php echo $filteredProductsCount; ?> of <?php echo $totalProductsCount; ?> products
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Section -->
        <section class="py-12">
            <div class="container mx-auto px-4">
                <!-- Filter Sidebar -->
                <div class="mb-8">
                    <?php include 'component/ui/filter-sidebar.php'; ?>
                </div>

                <!-- Loading State -->
                <?php if ($loading): ?>
                    <div class="text-center py-20">
                        <?php echo renderLoadingSpinner(['size' => 'lg', 'variant' => 'primary']); ?>
                    </div>
                <?php endif; ?>

                <!-- Error State -->
                <?php if ($error): ?>
                    <div class="text-center py-20">
                        <?php echo renderErrorState([
                            'error' => $error,
                            'onRetry' => 'window.location.reload()'
                        ]); ?>
                    </div>
                <?php endif; ?>

                <!-- Products Grid -->
                <?php if (!$loading && !$error): ?>
                    <?php if ($filteredProductsCount > 0): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                            <?php foreach ($products as $index => $product): ?>
                                <?php 
                                // Set the current product for the ProductCard component
                                $GLOBALS['currentProduct'] = $product;
                                ?>
                                <?php include 'component/product/product-card.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-20">
                            <h3 class="text-2xl font-semibold text-gray-900 mb-4">
                                No Products Available
                            </h3>
                            <p class="text-gray-400 mb-6 text-lg">
                                <?php 
                                if ($totalProductsCount === 0) {
                                    echo "We're currently setting up our product catalog. Check back soon!";
                                } else {
                                    echo "No products found in the " . ucfirst($selectedCategory) . " category.";
                                }
                                ?>
                            </p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Include Footer -->
    <?php include 'component/footer.php'; ?>

    <!-- Include Toast Component -->
    <?php include 'component/ui/toast.php'; ?>

    <!-- Include Auth Modals -->
    <?php include 'auth/login.php'; ?>
    <?php include 'auth/register.php'; ?>

    <!-- JavaScript for mobile menu -->
    <script>
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            
            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html>
