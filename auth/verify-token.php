<?php
// Token Verification Endpoint
// Validates tokens sent from localStorage

require_once 'auth-helper.php';
startSessionIfNotStarted();

// Set JSON response header
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? null;

if (!$token) {
    echo json_encode(['valid' => false, 'error' => 'Token is required']);
    exit;
}

// Validate token
$isValid = validateToken($token);

if ($isValid) {
    echo json_encode(['valid' => true, 'user' => getCurrentUser()]);
} else {
    echo json_encode(['valid' => false, 'error' => 'Invalid token']);
}
?>
