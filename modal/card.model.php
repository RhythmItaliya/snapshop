<?php
class Card {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        // Removed automatic table creation to prevent conflicts
    }
    
    // Create card table if it doesn't exist
    public function createTable() {
        // Create cards table (without foreign key initially)
        $sql = "CREATE TABLE IF NOT EXISTS cards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            card_id VARCHAR(255) UNIQUE NOT NULL,
            brand VARCHAR(50),
            last4 INT NOT NULL,
            exp_month INT NOT NULL,
            exp_year INT,
            fingerprint VARCHAR(255),
            user_id INT NOT NULL,
            customer VARCHAR(255),
            country VARCHAR(100),
            name VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($this->conn->query($sql) === FALSE) {
            throw new Exception("Error creating cards table: " . $this->conn->error);
        }
        
        // Add foreign key constraint after table exists
        $this->addForeignKeys();
    }
    
    private function addForeignKeys() {
        // Check if foreign key constraint already exists before adding it
        $this->addForeignKeyIfNotExists('cards', 'fk_cards_user_id', 'user_id', 'users', 'id');
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
