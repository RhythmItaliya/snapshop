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

// Check if this is an AJAX request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // AJAX request - return JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
} else {
    // Direct browser access - redirect to homepage with success message
    header('Location: /snapshop/?logout=success');
}
exit;
?>
