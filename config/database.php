<?php
// Load environment configuration
require_once __DIR__ . '/Env.php';

// Database configuration from environment
define('DB_HOST', Env::db('host'));
define('DB_NAME', Env::db('name'));
define('DB_USER', Env::db('user'));
define('DB_PASS', Env::db('pass'));
define('DB_CHARSET', Env::db('charset'));

// Include database setup functions
require_once __DIR__ . '/database-setup.php';

/**
 * Auto-setup database and tables
 * @return mysqli|false Database connection or false on failure
 */
function getDatabaseConnection() {
    try {
        // First try to connect without database name to create it if needed
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Check if database exists, create if it doesn't
        $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
        
        if ($result->num_rows === 0) {
            // Database doesn't exist, create it
            if ($conn->query("CREATE DATABASE " . DB_NAME . " CHARACTER SET " . DB_CHARSET) === TRUE) {
                error_log("Database '" . DB_NAME . "' created successfully");
            } else {
                throw new Exception("Error creating database: " . $conn->error);
            }
        }
        
        // Close connection and reconnect to the specific database
        $conn->close();
        
        // Now connect to the specific database
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Connection to database failed: " . $conn->connect_error);
        }
        
        // Set charset
        $conn->set_charset(DB_CHARSET);
        
        // Auto-setup tables and seed data
        autoSetupDatabase($conn);
        
        return $conn;
        
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return false;
    }
}

/**
 * Auto-setup database tables and seed data
 * @param mysqli $conn Database connection
 */
function autoSetupDatabase($conn) {
    try {
        error_log("Starting auto-setup of database tables...");
        
        // Load all model files to create tables
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
        try {
            error_log("Creating users table...");
            $userModel = new User($conn);
            $userModel->createTable();        // users table first
        } catch (Exception $e) {
            error_log("Error creating users table: " . $e->getMessage());
        }
        
        try {
            error_log("Creating products table...");
            $productModel = new Product($conn);
            $productModel->createTable();      // products table second
        } catch (Exception $e) {
            error_log("Error creating products table: " . $e->getMessage());
        }
        
        try {
            error_log("Creating admin table...");
            $adminModel = new Admin($conn);
            $adminModel->createTable();        // admins table
        } catch (Exception $e) {
            error_log("Error creating admin table: " . $e->getMessage());
        }
        
        try {
            error_log("Creating card table...");
            $cardModel = new Card($conn);
            $cardModel->createTable();         // cards table
        } catch (Exception $e) {
            error_log("Error creating card table: " . $e->getMessage());
        }
        
        try {
            error_log("Creating cart table...");
            $cartModel = new Cart($conn);
            $cartModel->createTable();         // carts and cart_items tables
        } catch (Exception $e) {
            error_log("Error creating cart table: " . $e->getMessage());
        }
        
        try {
            error_log("Creating contact table...");
            $contactModel = new Contact($conn);
            $contactModel->createTable();      // contacts table
        } catch (Exception $e) {
            error_log("Error creating contact table: " . $e->getMessage());
        }
        
        try {
            error_log("Creating order table...");
            $orderModel = new Order($conn);
            $orderModel->createTable();        // orders and order_items tables
        } catch (Exception $e) {
            error_log("Error creating order table: " . $e->getMessage());
        }
        
        try {
            error_log("Creating payment method table...");
            $paymentMethodModel = new PaymentMethod($conn);
            $paymentMethodModel->createTable(); // payment_methods table
        } catch (Exception $e) {
            error_log("Error creating payment method table: " . $e->getMessage());
        }
        
        try {
            error_log("Creating wishlist table...");
            $wishlistModel = new Wishlist($conn);
            $wishlistModel->createTable();     // wishlists and wishlist_products tables
        } catch (Exception $e) {
            error_log("Error creating wishlist table: " . $e->getMessage());
        }
        
        // Load and run product seeder
        try {
            error_log("Seeding product data...");
            require_once __DIR__ . '/product-seeder.php';
            seedProducts($conn);
        } catch (Exception $e) {
            error_log("Error seeding products: " . $e->getMessage());
        }
        
        error_log("Database auto-setup completed successfully!");
        
    } catch (Exception $e) {
        error_log("Database auto-setup error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        // Don't crash the site, just log the error
    }
}

/**
 * Alias for setupDatabase for backward compatibility
 */
function setupDatabase() {
    return getDatabaseConnection();
}
?>
