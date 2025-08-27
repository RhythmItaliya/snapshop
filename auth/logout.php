<?php
// Logout functionality
session_start();

// Clear all session data
session_unset();
session_destroy();

// Clear any cookies if they exist
if (isset($_COOKIE['token'])) {
    setcookie('token', '', time() - 3600, '/');
}

// Return JSON response for JavaScript handling
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
exit;
?>
