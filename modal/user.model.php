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
    
    public function userExists($username) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    public function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    public function createUser($userData) {
        try {
            // Generate unique customer ID
            $customerId = 'CUST_' . uniqid() . '_' . time();
            
            $sql = "INSERT INTO users (username, first_name, last_name, email, password, customer_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                error_log("Prepare failed: " . $this->conn->error);
                return false;
            }
            
            $stmt->bind_param("ssssss", 
                $userData['username'], 
                $userData['first_name'], 
                $userData['last_name'], 
                $userData['email'], 
                $userData['password'], 
                $customerId
            );
            
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Execute failed: " . $stmt->error);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("createUser exception: " . $e->getMessage());
            return false;
        }
    }
    
    public function authenticateUser($username, $password) {
        $stmt = $this->conn->prepare("SELECT id, username, email, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        
        return false;
    }
}
?>
