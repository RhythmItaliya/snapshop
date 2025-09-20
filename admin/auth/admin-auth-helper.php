<?php
/**
 * Admin Authentication Helper
 * Handles admin authentication, token management, and session security
 */

class AdminAuthHelper {
    
    /**
     * Generate a secure admin token
     * @return string
     */
    public static function generateAdminToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Set admin session with token
     * @param array $admin Admin data from database
     * @return bool
     */
    public static function setAdminSession($admin) {
        if (!$admin || !isset($admin['id'])) {
            return false;
        }
        
        // Generate secure token
        $token = self::generateAdminToken();
        
        // Set session data
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_token'] = $token;
        $_SESSION['admin_login_time'] = time();
        $_SESSION['admin_last_activity'] = time();
        
        // Store token in localStorage via JavaScript
        return $token;
    }
    
    /**
     * Validate admin session and token
     * @return bool|array Returns admin data if valid, false otherwise
     */
    public static function validateAdminSession() {
        // Check if admin session exists
        if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_token'])) {
            return false;
        }
        
        // Check session timeout (8 hours)
        $timeout = 8 * 60 * 60; // 8 hours in seconds
        if (time() - $_SESSION['admin_login_time'] > $timeout) {
            self::destroyAdminSession();
            return false;
        }
        
        // Check inactivity timeout (30 minutes)
        $inactivity_timeout = 30 * 60; // 30 minutes in seconds
        if (time() - $_SESSION['admin_last_activity'] > $inactivity_timeout) {
            self::destroyAdminSession();
            return false;
        }
        
        // Update last activity
        $_SESSION['admin_last_activity'] = time();
        
        // Return admin data
        return [
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
            'email' => $_SESSION['admin_email'],
            'role' => $_SESSION['admin_role'],
            'token' => $_SESSION['admin_token']
        ];
    }
    
    /**
     * Check if admin is authenticated
     * @return bool
     */
    public static function isAdminAuthenticated() {
        return self::validateAdminSession() !== false;
    }
    
    /**
     * Require admin authentication (redirect if not authenticated)
     */
    public static function requireAdminAuth() {
        if (!self::isAdminAuthenticated()) {
            header('Location: /snapshop/admin/auth/login.php');
            exit;
        }
    }
    
    /**
     * Destroy admin session
     */
    public static function destroyAdminSession() {
        // Unset all admin session variables
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_email']);
        unset($_SESSION['admin_role']);
        unset($_SESSION['admin_token']);
        unset($_SESSION['admin_login_time']);
        unset($_SESSION['admin_last_activity']);
        
        // Destroy the session
        session_destroy();
    }
    
    /**
     * Get admin data from session
     * @return array|false
     */
    public static function getAdminData() {
        return self::validateAdminSession();
    }
}
?>
