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
 * Simple database connection function
 * @return mysqli|false Database connection or false on failure
 */
function getDatabaseConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset
        $conn->set_charset(DB_CHARSET);
        
        return $conn;
        
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return false;
    }
}

/**
 * Alias for setupDatabase for backward compatibility
 */
function setupDatabase() {
    return getDatabaseConnection();
}
?>
