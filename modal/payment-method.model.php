<?php
class PaymentMethod {
    private $conn;
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
        $this->createTable();
    }
    
    private function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS payment_methods (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id VARCHAR(255) NOT NULL,
            payment_method_id VARCHAR(255) NOT NULL,
            card_brand ENUM('Visa', 'MasterCard', 'American Express', 'Discover') DEFAULT 'Visa',
            last4 VARCHAR(4) NOT NULL,
            exp_month INT NOT NULL CHECK (exp_month >= 1 AND exp_month <= 12),
            exp_year INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_customer_id (customer_id),
            INDEX idx_payment_method_id (payment_method_id)
        )";
        
        if ($this->conn->query($sql) === FALSE) {
            throw new Exception("Error creating payment_methods table: " . $this->conn->error);
        }
    }
}
?>
