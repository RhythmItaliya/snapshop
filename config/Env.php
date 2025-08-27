<?php
class Env {
    private static $config = null;
    
    // Load environment configuration
    public static function load() {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/environment.php';
        }
        return self::$config;
    }
    
    // Get environment variable
    public static function get($key, $default = null) {
        $config = self::load();
        return $config[$key] ?? $default;
    }
    
    // Get all environment variables
    public static function all() {
        return self::load();
    }
    
    // Check if environment variable exists
    public static function has($key) {
        $config = self::load();
        return isset($config[$key]);
    }
    
    // Get database configuration
    public static function db($key = null) {
        $db_config = [
            'host' => self::get('DB_HOST'),
            'name' => self::get('DB_NAME'),
            'user' => self::get('DB_USER'),
            'pass' => self::get('DB_PASS'),
            'charset' => self::get('DB_CHARSET')
        ];
        
        if ($key === null) {
            return $db_config;
        }
        
        return $db_config[$key] ?? null;
    }
    
    // Get JWT configuration
    public static function jwt($key = null) {
        $jwt_config = [
            'secret' => self::get('JWT_SECRET'),
            'expires_in' => self::get('JWT_EXPIRES_IN')
        ];
        
        if ($key === null) {
            return $jwt_config;
        }
        
        return $jwt_config[$key] ?? null;
    }
    
    // Get Cloudinary configuration
    public static function cloudinary($key = null) {
        $cloudinary_config = [
            'cloud_name' => self::get('CLOUDINARY_CLOUD_NAME'),
            'api_key' => self::get('CLOUDINARY_API_KEY'),
            'api_secret' => self::get('CLOUDINARY_API_SECRET'),
            'folder' => self::get('CLOUDINARY_FOLDER')
        ];
        
        if ($key === null) {
            return $cloudinary_config;
        }
        
        return $cloudinary_config[$key] ?? null;
    }
    
    // Get Razorpay configuration
    public static function razorpay($key = null) {
        $razorpay_config = [
            'key_id' => self::get('RAZORPAY_KEY_ID'),
            'key_secret' => self::get('RAZORPAY_KEY_SECRET')
        ];
        
        if ($key === null) {
            return $razorpay_config;
        }
        
        return $razorpay_config[$key] ?? null;
    }
    
    // Get admin configuration
    public static function admin($key = null) {
        $admin_config = [
            'username' => self::get('ADMIN_USERNAME'),
            'email' => self::get('ADMIN_EMAIL'),
            'password' => self::get('ADMIN_PASSWORD'),
            'role' => self::get('ADMIN_ROLE'),
            'permissions' => [
                'users' => self::get('ADMIN_PERMISSION_USERS'),
                'orders' => self::get('ADMIN_PERMISSION_ORDERS'),
                'products' => self::get('ADMIN_PERMISSION_PRODUCTS'),
                'payments' => self::get('ADMIN_PERMISSION_PAYMENTS'),
                'settings' => self::get('ADMIN_PERMISSION_SETTINGS'),
                'contacts' => self::get('ADMIN_PERMISSION_CONTACTS')
            ]
        ];
        
        if ($key === null) {
            return $admin_config;
        }
        
        if ($key === 'permissions') {
            return $admin_config['permissions'];
        }
        
        return $admin_config[$key] ?? null;
    }
    
    // Check if in development mode
    public static function isDevelopment() {
        return self::get('NODE_ENV') === 'development';
    }
    
    // Check if in production mode
    public static function isProduction() {
        return self::get('NODE_ENV') === 'production';
    }
}
?>
