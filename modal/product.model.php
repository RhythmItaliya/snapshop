<?php
class Product {
    private $conn;
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
        // Removed automatic table creation to prevent conflicts
    }
    
    // Create products table if it doesn't exist
    public function createTable() {
        // Create products table first (without foreign keys initially)
        $products_sql = "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            category VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL CHECK (price >= 0),
            high_price DECIMAL(10,2) NOT NULL CHECK (high_price >= 0),
            stock INT NOT NULL DEFAULT 0 CHECK (stock >= 0),
            image VARCHAR(255) NOT NULL,
            gender ENUM('men', 'women', 'unisex') NOT NULL,
            discount DECIMAL(5,2) NOT NULL DEFAULT 0.00 CHECK (discount >= 0),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_category (category),
            INDEX idx_gender (gender),
            INDEX idx_price (price)
        )";
        
        if ($this->conn->query($products_sql) === FALSE) {
            throw new Exception("Error creating products table: " . $this->conn->error);
        }
        
        // Create product_sizes table
        $product_sizes_sql = "CREATE TABLE IF NOT EXISTS product_sizes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            size ENUM('XS', 'S', 'M', 'L', 'XL', 'XXL') NOT NULL
        )";
        
        if ($this->conn->query($product_sizes_sql) === FALSE) {
            throw new Exception("Error creating product_sizes table: " . $this->conn->error);
        }
        
        // Create product_colors table
        $product_colors_sql = "CREATE TABLE IF NOT EXISTS product_colors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            color VARCHAR(50) NOT NULL
        )";
        
        if ($this->conn->query($product_colors_sql) === FALSE) {
            throw new Exception("Error creating product_colors table: " . $this->conn->error);
        }
        
        // Add foreign key constraints after all tables exist
        $this->addForeignKeys();
    }
    
    private function addForeignKeys() {
        // Check if foreign key constraints already exist before adding them
        $this->addForeignKeyIfNotExists('product_sizes', 'fk_product_sizes_product_id', 'product_id', 'products', 'id');
        $this->addForeignKeyIfNotExists('product_colors', 'fk_product_colors_product_id', 'product_id', 'products', 'id');
    }
    
    private function addForeignKeyIfNotExists($table, $constraint_name, $column, $referenced_table, $referenced_column) {
        // Check if constraint already exists
        $check_sql = "SELECT COUNT(*) as count FROM information_schema.TABLE_CONSTRAINTS 
                      WHERE CONSTRAINT_SCHEMA = DATABASE() 
                      AND TABLE_NAME = '$table' 
                      AND CONSTRAINT_NAME = '$constraint_name'";
        
        $result = $this->conn->query($check_sql);
        if ($result && $result->fetch_assoc()['count'] == 0) {
            // Constraint doesn't exist, add it
            $fk_sql = "ALTER TABLE $table 
                       ADD CONSTRAINT $constraint_name 
                       FOREIGN KEY ($column) REFERENCES $referenced_table($referenced_column) 
                       ON DELETE CASCADE";
            $this->conn->query($fk_sql);
        }
    }
    
    // Get all products
    public function getAllProducts($limit = null) {
        $sql = "SELECT p.*, 
                       GROUP_CONCAT(DISTINCT ps.size) as sizes,
                       GROUP_CONCAT(DISTINCT pc.color) as colors
                FROM products p
                LEFT JOIN product_sizes ps ON p.id = ps.product_id
                LEFT JOIN product_colors pc ON p.id = pc.product_id
                GROUP BY p.id
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $result = $this->conn->query($sql);
        if (!$result) {
            throw new Exception("Error fetching products: " . $this->conn->error);
        }
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            // Convert comma-separated sizes and colors to arrays
            $sizes = $row['sizes'] ? explode(',', $row['sizes']) : [];
            $colors = $row['colors'] ? explode(',', $row['colors']) : [];
            
            $products[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'category' => $row['category'],
                'price' => $row['price'],
                'highPrice' => $row['high_price'],
                'stock' => $row['stock'],
                'image' => $row['image'],
                'gender' => $row['gender'],
                'discount' => $row['discount'],
                'createdAt' => $row['created_at'],
                'sizes' => $sizes,
                'colors' => $colors
            ];
        }
        
        return $products;
    }
    
    // Get trending products (most recent or popular)
    public function getTrendingProducts($limit = 18) {
        return $this->getAllProducts($limit);
    }
    
    // Get products by category
    public function getProductsByCategory($category, $limit = null) {
        $sql = "SELECT p.*, 
                       GROUP_CONCAT(DISTINCT ps.size) as sizes,
                       GROUP_CONCAT(DISTINCT pc.color) as colors
                FROM products p
                LEFT JOIN product_sizes ps ON p.id = ps.product_id
                LEFT JOIN product_colors pc ON p.id = pc.product_id
                WHERE p.category = ?
                GROUP BY p.id
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            throw new Exception("Error fetching products by category: " . $this->conn->error);
        }
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            // Convert comma-separated sizes and colors to arrays
            $sizes = $row['sizes'] ? explode(',', $row['sizes']) : [];
            $colors = $row['colors'] ? explode(',', $row['colors']) : [];
            
            $products[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'category' => $row['category'],
                'price' => $row['price'],
                'highPrice' => $row['high_price'],
                'stock' => $row['stock'],
                'image' => $row['image'],
                'gender' => $row['gender'],
                'discount' => $row['discount'],
                'createdAt' => $row['created_at'],
                'sizes' => $sizes,
                'colors' => $colors
            ];
        }
        
        return $products;
    }
    
    // Get product by ID
    public function getProductById($id) {
        $sql = "SELECT p.*, 
                       GROUP_CONCAT(DISTINCT ps.size) as sizes,
                       GROUP_CONCAT(DISTINCT pc.color) as colors
                FROM products p
                LEFT JOIN product_sizes ps ON p.id = ps.product_id
                LEFT JOIN product_colors pc ON p.id = pc.product_id
                WHERE p.id = ?
                GROUP BY p.id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            throw new Exception("Error fetching product: " . $this->conn->error);
        }
        
        $row = $result->fetch_assoc();
        if (!$row) {
            return null;
        }
        
        // Convert comma-separated sizes and colors to arrays
        $sizes = $row['sizes'] ? explode(',', $row['sizes']) : [];
        $colors = $row['colors'] ? explode(',', $row['colors']) : [];
        
        return [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'category' => $row['category'],
            'price' => $row['price'],
            'highPrice' => $row['high_price'],
            'stock' => $row['stock'],
            'image' => $row['image'],
            'gender' => $row['gender'],
            'discount' => $row['discount'],
            'createdAt' => $row['created_at'],
            'sizes' => $sizes,
            'colors' => $colors
        ];
    }
    
    // Delete product by ID
    public function deleteProduct($id) {
        // Start transaction
        $this->conn->begin_transaction();
        
        try {
            // Delete related records first (due to foreign key constraints)
            $delete_sizes_sql = "DELETE FROM product_sizes WHERE product_id = ?";
            $stmt = $this->conn->prepare($delete_sizes_sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $delete_colors_sql = "DELETE FROM product_colors WHERE product_id = ?";
            $stmt = $this->conn->prepare($delete_colors_sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            // Delete the main product
            $delete_product_sql = "DELETE FROM products WHERE id = ?";
            $stmt = $this->conn->prepare($delete_product_sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
}
?>
