<?php
class Contact {
    private $conn;
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
        $this->createTable();
    }
    
    private function createTable() {
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
