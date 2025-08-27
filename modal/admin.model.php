<?php
class Admin {
    private $conn;
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
        // Removed automatic table creation to prevent conflicts
    }
    
    // Create admin table if it doesn't exist
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
            permissions_users BOOLEAN DEFAULT TRUE,
            permissions_orders BOOLEAN DEFAULT TRUE,
            permissions_products BOOLEAN DEFAULT TRUE,
            permissions_payments BOOLEAN DEFAULT TRUE,
            permissions_settings BOOLEAN DEFAULT FALSE,
            is_active BOOLEAN DEFAULT TRUE,
            last_login DATETIME NULL,
            login_attempts INT DEFAULT 0,
            lock_until DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($this->conn->query($sql) === TRUE) {
            // Table created successfully
        } else {
            // Error creating table
        }
        
        // Create default admin after table is created
        $this->createDefaultAdmin();
    }
    
    private function createDefaultAdmin() {
        // Check if admin already exists
        $check_sql = "SELECT COUNT(*) as count FROM admins WHERE username = ? OR email = ?";
        $check_stmt = $this->conn->prepare($check_sql);
        
        // Get admin details from environment
        require_once __DIR__ . '/../config/Env.php';
        $admin_username = Env::admin('username');
        $admin_email = Env::admin('email');
        $admin_password = Env::admin('password');
        $admin_role = Env::admin('role');
        
        $check_stmt->bind_param("ss", $admin_username, $admin_email);
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();
        
        if ($result['count'] == 0) {
            // Create default admin
            $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
            
            $insert_sql = "INSERT INTO admins (username, email, password, role, 
                           permissions_users, permissions_orders, permissions_products, 
                           permissions_payments, permissions_settings) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $insert_stmt = $this->conn->prepare($insert_sql);
            
            $permissions_users = Env::admin('permission_users') ? 1 : 0;
            $permissions_orders = Env::admin('permission_orders') ? 1 : 0;
            $permissions_products = Env::admin('permission_products') ? 1 : 0;
            $permissions_payments = Env::admin('permission_payments') ? 1 : 0;
            $permissions_settings = Env::admin('permission_settings') ? 1 : 0;
            
            $insert_stmt->bind_param("sssssssss", 
                $admin_username, $admin_email, $hashed_password, $admin_role,
                $permissions_users, $permissions_orders, $permissions_products,
                $permissions_payments, $permissions_settings
            );
            
            $insert_stmt->execute();
        }
    }
}
?>
