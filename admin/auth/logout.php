<?php
// Admin Logout
require_once __DIR__ . '/../../auth/auth-helper.php';
startSessionIfNotStarted();

// Use AdminAuthHelper to properly destroy session
require_once 'admin-auth-helper.php';
AdminAuthHelper::destroyAdminSession();

// Redirect to admin login with JavaScript to clear localStorage
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
</head>
<body>
    <script>
        // Clear localStorage
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_login_time');
        
        // Redirect to login page
        window.location.href = '/snapshop/admin/auth/login.php';
    </script>
</body>
</html>
