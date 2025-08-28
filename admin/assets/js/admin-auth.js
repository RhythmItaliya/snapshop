/**
 * Admin Authentication JavaScript Utility
 * Handles token validation, session management, and security checks
 */

class AdminAuth {
    
    /**
     * Check if admin token is valid
     * @returns {boolean}
     */
    static isTokenValid() {
        const token = localStorage.getItem('admin_token');
        const loginTime = localStorage.getItem('admin_login_time');
        
        if (!token || !loginTime) {
            return false;
        }
        
        // Check if token is expired (8 hours)
        const currentTime = Math.floor(Date.now() / 1000);
        const tokenAge = currentTime - parseInt(loginTime);
        const maxAge = 8 * 60 * 60; // 8 hours in seconds
        
        return tokenAge <= maxAge;
    }
    
    /**
     * Validate token and redirect if invalid
     */
    static validateToken() {
        if (!this.isTokenValid()) {
            this.clearAuth();
            window.location.href = '/snapshop/admin/auth/login.php';
            return false;
        }
        
        // Update login time to extend session
        localStorage.setItem('admin_login_time', Math.floor(Date.now() / 1000));
        return true;
    }
    
    /**
     * Clear authentication data
     */
    static clearAuth() {
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_login_time');
    }
    
    /**
     * Logout admin
     */
    static logout() {
        this.clearAuth();
        window.location.href = '/snapshop/admin/auth/logout.php';
    }
    
    /**
     * Get admin token
     * @returns {string|null}
     */
    static getToken() {
        return localStorage.getItem('admin_token');
    }
    
    /**
     * Check token validity every 5 minutes
     */
    static startTokenValidation() {
        // Validate token every 5 minutes
        setInterval(() => {
            this.validateToken();
        }, 5 * 60 * 1000);
        
        // Also validate on page visibility change
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.validateToken();
            }
        });
    }
}

// Auto-start token validation when page loads
document.addEventListener('DOMContentLoaded', function() {
    AdminAuth.validateToken();
    AdminAuth.startTokenValidation();
});

// Add logout functionality to logout buttons
document.addEventListener('DOMContentLoaded', function() {
    const logoutButtons = document.querySelectorAll('[href*="logout.php"]');
    logoutButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            AdminAuth.logout();
        });
    });
});
