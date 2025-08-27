<?php
// Automatic database setup and creation
// Note: setupDatabase function is now defined in database.php

// Function to create tables and seed data
function initializeDatabase($conn) {
    try {
        // Load all modal files to create tables
        require_once __DIR__ . '/../modal/user.model.php';
        require_once __DIR__ . '/../modal/product.model.php';
        require_once __DIR__ . '/../modal/admin.model.php';
        require_once __DIR__ . '/../modal/card.model.php';
        require_once __DIR__ . '/../modal/cart.model.php';
        require_once __DIR__ . '/../modal/contact.model.php';
        require_once __DIR__ . '/../modal/order.model.php';
        require_once __DIR__ . '/../modal/payment-method.model.php';
        require_once __DIR__ . '/../modal/wishlist.model.php';
        
        // Initialize models in order (tables that others depend on first)
        new User($conn);        // users table first
        new Product($conn);      // products table second
        new Admin($conn);        // admins table
        new Card($conn);         // cards table
        new Cart($conn);         // carts and cart_items tables
        new Contact($conn);      // contacts table
        new Order($conn);        // orders and order_items tables
        new PaymentMethod($conn); // payment_methods table
        new Wishlist($conn);     // wishlists and wishlist_products tables
        
        // Load and run product seeder
        require_once __DIR__ . '/product-seeder.php';
        seedProducts($conn);
        
        return $conn;
        
    } catch (Exception $e) {
        die("Database Error: " . $e->getMessage());
    }
}
?>
