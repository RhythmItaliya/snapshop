<?php
// Auth Helper Functions
// Provides utility functions for authentication

function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['token']);
}

function getCurrentUser() {
    if (!isUserLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['email'] ?? ''
    ];
}

function validateToken($token) {
    // In production, implement proper JWT validation
    // For now, just check if token exists in session
    return isset($_SESSION['token']) && $_SESSION['token'] === $token;
}

function requireLogin() {
    if (!isUserLoggedIn()) {
        header('Location: /snapshop/');
        exit;
    }
}

function logoutUser() {
    session_unset();
    session_destroy();
    
    // Clear any cookies
    if (isset($_COOKIE['token'])) {
        setcookie('token', '', time() - 3600, '/');
    }
}
?>
