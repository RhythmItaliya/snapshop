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
    
    // Get all contacts for admin
    public function getAllContacts() {
        $sql = "SELECT * FROM contacts ORDER BY created_at DESC";
        
        $result = $this->conn->query($sql);
        if (!$result) {
            return [];
        }
        
        $contacts = [];
        while ($row = $result->fetch_assoc()) {
            $contacts[] = $row;
        }
        
        return $contacts;
    }
    
    // Update contact status
    public function updateContactStatus($contactId, $newStatus) {
        $sql = "UPDATE contacts SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $newStatus, $contactId);
        
        return $stmt->execute();
    }
    
    // Get contact by ID
    public function getContactById($contactId) {
        $sql = "SELECT * FROM contacts WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $contactId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    // Create new contact
    public function createContact($contactData) {
        $sql = "INSERT INTO contacts (name, email, message, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", 
            $contactData['name'],
            $contactData['email'],
            $contactData['message'],
            $contactData['ip_address'] ?? '',
            $contactData['user_agent'] ?? ''
        );
        
        return $stmt->execute();
    }
}
?>
