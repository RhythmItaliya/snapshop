<?php
class Cart {
    private $conn;
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
        $this->createTable();
    }
    
    private function createTable() {
        // Create carts table first (without foreign key initially)
        $carts_sql = "CREATE TABLE IF NOT EXISTS carts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            total_quantity INT DEFAULT 0,
            total_amount DECIMAL(10,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($this->conn->query($carts_sql) === FALSE) {
            throw new Exception("Error creating carts table: " . $this->conn->error);
        }
        
        // Create cart_items table
        $cart_items_sql = "CREATE TABLE IF NOT EXISTS cart_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cart_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            price DECIMAL(10,2) NOT NULL,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($this->conn->query($cart_items_sql) === FALSE) {
            throw new Exception("Error creating cart_items table: " . $this->conn->error);
        }
        
        // Add foreign key constraints after both tables exist
        $this->addForeignKeys();
    }
    
    private function addForeignKeys() {
        // Check if foreign key constraints already exist before adding them
        $this->addForeignKeyIfNotExists('carts', 'fk_carts_user_id', 'user_id', 'users', 'id');
        $this->addForeignKeyIfNotExists('cart_items', 'fk_cart_items_cart_id', 'cart_id', 'carts', 'id');
        $this->addForeignKeyIfNotExists('cart_items', 'fk_cart_items_product_id', 'product_id', 'products', 'id');
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
