<?php
class Wishlist {
    private $conn;
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
        $this->createTable();
    }
    
    private function createTable() {
        // Create wishlists table first (without foreign keys initially)
        $wishlists_sql = "CREATE TABLE IF NOT EXISTS wishlists (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($this->conn->query($wishlists_sql) === FALSE) {
            throw new Exception("Error creating wishlists table: " . $this->conn->error);
        }
        
        // Create wishlist_products table
        $wishlist_products_sql = "CREATE TABLE IF NOT EXISTS wishlist_products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            wishlist_id INT NOT NULL,
            product_id INT NOT NULL,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($this->conn->query($wishlist_products_sql) === FALSE) {
            throw new Exception("Error creating wishlist_products table: " . $this->conn->error);
        }
        
        // Add foreign key constraints after both tables exist
        $this->addForeignKeys();
    }
    
    private function addForeignKeys() {
        // Check if foreign key constraints already exist before adding them
        $this->addForeignKeyIfNotExists('wishlists', 'fk_wishlists_user_id', 'user_id', 'users', 'id');
        $this->addForeignKeyIfNotExists('wishlist_products', 'fk_wishlist_products_wishlist_id', 'wishlist_id', 'wishlists', 'id');
        $this->addForeignKeyIfNotExists('wishlist_products', 'fk_wishlist_products_product_id', 'product_id', 'products', 'id');
    }
    
    private function addForeignKeyIfNotExists($table, $constraint_name, $column, $referenced_table, $referenced_column) {
        // Check if constraint already exists
        $check_sql = "SELECT COUNT(*) as count FROM information_schema.TABLE_CONSTRAINTS 
                      WHERE CONSTRAINT_SCHEMA = DATABASE() 
                      AND TABLE_NAME = '$table' 
                      AND CONSTRAINT_NAME = '$constraint_name'";
        
        $result = $this->conn->query($check_sql);
        if ($result && $result->fetch_assoc()['count'] == 0) {
            // Constraint doesn't exist, add it
            $fk_sql = "ALTER TABLE $table 
                       ADD CONSTRAINT $constraint_name 
                       FOREIGN KEY ($column) REFERENCES $referenced_table($referenced_column) 
                       ON DELETE CASCADE";
            $this->conn->query($fk_sql);
        }
    }
}
?>
