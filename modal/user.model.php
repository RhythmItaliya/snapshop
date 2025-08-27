<?php
// User Model - Handles user data and profile management

class User {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Create users table if it doesn't exist
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(50),
            last_name VARCHAR(50),
            phone VARCHAR(20),
            address JSON,
            avatar VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        return $this->conn->query($sql);
    }
    
    // Get user by ID
    public function getUserById($userId) {
        $stmt = $this->conn->prepare("SELECT id, username, email, first_name, last_name, phone, address, avatar, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Decode JSON address if it exists
            if ($user['address']) {
                $user['address'] = json_decode($user['address'], true);
            } else {
                $user['address'] = [
                    'street_address' => '',
                    'apartment' => '',
                    'city' => '',
                    'state' => '',
                    'country' => '',
                    'zip_code' => ''
                ];
            }
            return $user;
        }
        return null;
    }
    
    // Get user by username
    public function getUserByUsername($username) {
        $stmt = $this->conn->prepare("SELECT id, username, email, password, first_name, last_name, phone, address, avatar FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user['address']) {
                $user['address'] = json_decode($user['address'], true);
            }
            return $user;
        }
        return null;
    }
    
    // Get user by email
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT id, username, email, password, first_name, last_name, phone, address, avatar FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user['address']) {
                $user['address'] = json_decode($user['address'], true);
            }
            return $user;
        }
        return null;
    }
    
    // Create new user
    public function createUser($userData) {
        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $address = json_encode($userData['address'] ?? []);
        
        $stmt->bind_param("sssssss", 
            $userData['username'],
            $userData['email'],
            $userData['password'],
            $userData['first_name'],
            $userData['last_name'],
            $userData['phone'],
            $address
        );
        
        return $stmt->execute();
    }
    
    // Update user profile
    public function updateProfile($userId, $updateData) {
        // Check if username or email already exists for other users
        if (isset($updateData['username'])) {
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->bind_param("si", $updateData['username'], $userId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                return false; // Username already exists
            }
        }
        
        if (isset($updateData['email'])) {
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $updateData['email'], $userId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                return false; // Email already exists
            }
        }
        
        // Prepare update fields
        $fields = [];
        $types = "";
        $values = [];
        
        if (isset($updateData['username'])) {
            $fields[] = "username = ?";
            $types .= "s";
            $values[] = $updateData['username'];
        }
        
        if (isset($updateData['first_name'])) {
            $fields[] = "first_name = ?";
            $types .= "s";
            $values[] = $updateData['first_name'];
        }
        
        if (isset($updateData['last_name'])) {
            $fields[] = "last_name = ?";
            $types .= "s";
            $values[] = $updateData['last_name'];
        }
        
        if (isset($updateData['email'])) {
            $fields[] = "email = ?";
            $types .= "s";
            $values[] = $updateData['email'];
        }
        
        if (isset($updateData['phone'])) {
            $fields[] = "phone = ?";
            $types .= "s";
            $values[] = $updateData['phone'];
        }
        
        if (isset($updateData['address'])) {
            $fields[] = "address = ?";
            $types .= "s";
            $values[] = json_encode($updateData['address']);
        }
        
        if (empty($fields)) {
            return false; // No fields to update
        }
        
        // Add user ID to values array
        $values[] = $userId;
        $types .= "i";
        
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        
        return $stmt->execute();
    }
    
    // Change user password
    public function changePassword($userId, $currentPassword, $newPassword) {
        // First verify current password
        $stmt = $this->conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $user = $result->fetch_assoc();
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return false;
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        
        return $stmt->execute();
    }
    
    // Verify user credentials
    public function verifyCredentials($username, $password) {
        $user = $this->getUserByUsername($username);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return null;
    }
    
    // Check if username exists
    public function usernameExists($username, $excludeUserId = null) {
        $sql = "SELECT id FROM users WHERE username = ?";
        $params = [$username];
        $types = "s";
        
        if ($excludeUserId) {
            $sql .= " AND id != ?";
            $params[] = $excludeUserId;
            $types .= "i";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        return $stmt->get_result()->num_rows > 0;
    }
    
    // Check if email exists
    public function emailExists($email, $excludeUserId = null) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $params = [$email];
        $types = "s";
        
        if ($excludeUserId) {
            $sql .= " AND id != ?";
            $params[] = $excludeUserId;
            $types .= "i";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        return $stmt->get_result()->num_rows > 0;
    }
    
    // Delete user
    public function deleteUser($userId) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        
        return $stmt->execute();
    }
}
?>
