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

// Redirect to home page
header('Location: /snapshop/');
exit;
?>
