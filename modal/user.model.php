<?php
class User {
    private $conn;
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
        $this->createTable();
    }
    
    private function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            first_name VARCHAR(50) DEFAULT '',
            last_name VARCHAR(50) DEFAULT '',
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            customer_id VARCHAR(255) UNIQUE NOT NULL,
            phone VARCHAR(20) DEFAULT '',
            street_address VARCHAR(100) DEFAULT '',
            apartment VARCHAR(50) DEFAULT '',
            city VARCHAR(50) DEFAULT '',
            state VARCHAR(50) DEFAULT '',
            country VARCHAR(50) DEFAULT '',
            zip_code VARCHAR(20) DEFAULT '',
            avatar VARCHAR(255) DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email),
            INDEX idx_customer_id (customer_id)
        )";
        
        if ($this->conn->query($sql) === TRUE) {
            // Table created successfully
        } else {
            // Error creating table
        }
    }
}
?>
