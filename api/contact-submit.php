<?php
// Contact Submit API - Handle contact form submissions

// Start session for user authentication
session_start();

// Set JSON response header
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($input['name']) || empty($input['email']) || empty($input['message'])) {
        throw new Exception('All fields are required');
    }
    
    // Sanitize input
    $name = trim($input['name']);
    $email = trim($input['email']);
    $message = trim($input['message']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Validate name length
    if (strlen($name) < 2 || strlen($name) > 100) {
        throw new Exception('Name must be between 2 and 100 characters');
    }
    
    // Validate message length
    if (strlen($message) < 10 || strlen($message) > 1000) {
        throw new Exception('Message must be between 10 and 1000 characters');
    }
    
    // Include database connection and contact model
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../modal/contact.model.php';
    
    $conn = setupDatabase();
    $contactModel = new Contact($conn);
    
    // Submit contact message
    $contactData = [
        'name' => $name,
        'email' => $email,
        'message' => $message
    ];
    
    $result = $contactModel->createContact($contactData);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your message! We\'ll get back to you within 24 hours.'
        ]);
    } else {
        throw new Exception('Failed to submit contact message');
    }
    
} catch (Exception $e) {
    // Error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
