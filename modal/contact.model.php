<?php
class Contact {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        // Removed automatic table creation to prevent conflicts
    }
    
    // Create contact table if it doesn't exist
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('pending', 'read', 'replied') DEFAULT 'pending',
            ip_address VARCHAR(45) DEFAULT '',
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email_created (email, created_at),
            INDEX idx_status_created (status, created_at)
        )";
        
        if ($this->conn->query($sql) === FALSE) {
            throw new Exception("Error creating contacts table: " . $this->conn->error);
        }
    }
}
?>
