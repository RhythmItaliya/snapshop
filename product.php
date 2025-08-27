<?php
// Product Detail Page - Standalone file for individual product pages
// This file handles the /product/{id} route

// Start session for user authentication
session_start();

// Fallback routing - check if we're accessing via direct URL
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$pathInfo = parse_url($requestUri, PHP_URL_PATH);

// Extract product ID from URL path if .htaccess isn't working
if (empty($_GET['id']) && preg_match('/\/product\/(\d+)/', $pathInfo, $matches)) {
    $_GET['id'] = $matches[1];
}

// Include necessary components
require_once __DIR__ . '/component/header.php';
require_once __DIR__ . '/component/product/product-details.php';
require_once __DIR__ . '/component/footer.php';
?>
