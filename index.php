<?php
// Main entry point - handles home page only
// Product and products routing is handled by .htaccess
session_start();

// Include required files for home page
require_once 'config/index.php';
$conn = setupDatabase();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="node_modules/aos/dist/aos.css">
    
    <!-- Font Awesome Icons 7.0.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
</head>
<body class="bg-light font-spartan text-primary">
    <!-- Header Component -->
    <?php include 'component/header.php'; ?>

    <!-- Main Content - Home Page Only -->
    <main>
        <!-- Hero Section -->
        <?php include 'component/home/hero.php'; ?>
        
        <!-- Service Info Section -->
        <?php include 'component/home/service-info.php'; ?>

        <!-- Featured Collections Section -->
        <?php include 'component/home/featured-collection.php'; ?>
        
        <!-- Trending Products Section -->
        <?php include 'component/home/trending-products.php'; ?>
        
        <!-- Repasse Collections Section -->
        <?php include 'component/home/repasse-collections.php'; ?>
        
        <!-- New Collection Section -->
        <?php include 'component/home/new-collection.php'; ?>
                
    </main>

    <!-- Footer Component -->
    <?php include 'component/footer.php'; ?>

    <!-- AOS JavaScript -->
    <script src="node_modules/aos/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    </script>
    
    <!-- Include Toast Notifications -->
    <?php include 'component/ui/toast.php'; ?>
    
    <!-- Include Login Modal -->
    <?php include 'auth/login.php'; ?>
    
    <!-- Include Register Modal -->
    <?php include 'auth/register.php'; ?>
</body>
</html>
