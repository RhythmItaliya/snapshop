<?php
// Product Detail Page - Simple and direct
// Access via: /snapshop/product.php?id=13

// Start session for user authentication
session_start();

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect to products page if no ID
    header('Location: /snapshop/products.php');
    exit;
}

// Include necessary components
require_once __DIR__ . '/component/header.php';
require_once __DIR__ . '/component/product/product-details.php';
require_once __DIR__ . '/component/footer.php';
?>
