<?php
class Order {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        // Removed automatic table creation to prevent conflicts
    }
    
    // Create order tables if they don't exist
    public function createTable() {
        // Create orders table first (without foreign keys initially)
        $orders_sql = "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            order_number VARCHAR(50) UNIQUE,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('placed', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'placed',
            payment_method ENUM('razorpay') NOT NULL,
            payment_id VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($this->conn->query($orders_sql) === FALSE) {
            throw new Exception("Error creating orders table: " . $this->conn->error);
        }
        
        // Create order_items table
        $order_items_sql = "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL
        )";
        
        if ($this->conn->query($order_items_sql) === FALSE) {
            throw new Exception("Error creating order_items table: " . $this->conn->error);
        }
        
        // Add foreign key constraints after both tables exist
        $this->addForeignKeys();
    }
    
    private function addForeignKeys() {
        // Check if foreign key constraints already exist before adding them
        $this->addForeignKeyIfNotExists('orders', 'fk_orders_user_id', 'user_id', 'users', 'id');
        $this->addForeignKeyIfNotExists('order_items', 'fk_order_items_order_id', 'order_id', 'orders', 'id');
        $this->addForeignKeyIfNotExists('order_items', 'fk_order_items_product_id', 'product_id', 'products', 'id');
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
