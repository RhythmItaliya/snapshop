<?php
// Environment Configuration
return [
    // Server Configuration
    'PORT' => 5000,
    'NODE_ENV' => 'development',
    
    // Database Configuration
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'snapshop',
    'DB_USER' => 'demo',
    'DB_PASS' => '112203',
    'DB_CHARSET' => 'utf8mb4',
    
    // JWT Configuration
    'JWT_SECRET' => 'your_super_secret_jwt_key_here_change_this_in_production',
    'JWT_EXPIRES_IN' => '7d',
    
    // Cloudinary Configuration
    'CLOUDINARY_CLOUD_NAME' => 'ds9ufpxom',
    'CLOUDINARY_API_KEY' => '819183193299387',
    'CLOUDINARY_API_SECRET' => 'OIldsEhVgMuBOJc0lq45HHR7kRU',
    'CLOUDINARY_FOLDER' => 'snakshop_php',
        
    // Payment Gateway Keys
    'RAZORPAY_KEY_ID' => 'rzp_test_t8qeVD7fsffjfV',
    'RAZORPAY_KEY_SECRET' => 'wynTnpWG7jyaQq4fsZwbcIi9',
    
    // App Configuration
    'RAZORPAY_KEY' => 'rzp_test_t8qeVD7fsffjfV',
    'CURRENCY' => 'INR',
    'APP_NAME' => 'Snapshop',
    'APP_DESCRIPTION' => 'Snapshop Transaction',
    
    // Admin Configuration
    'ADMIN_USERNAME' => 'admin',
    'ADMIN_EMAIL' => 'admin@snapshop.com',
    'ADMIN_PASSWORD' => 'admin123',
    'ADMIN_ROLE' => 'super_admin',
    'ADMIN_PERMISSION_USERS' => true,
    'ADMIN_PERMISSION_ORDERS' => true,
    'ADMIN_PERMISSION_PRODUCTS' => true,
    'ADMIN_PERMISSION_PAYMENTS' => true,
    'ADMIN_PERMISSION_SETTINGS' => true,
    'ADMIN_PERMISSION_CONTACTS' => true
];
?>
