<?php
class Wishlist {
    private $conn;
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
        $this->createTable();
    }
    
    private function createTable() {
        // Create wishlists table first (without foreign keys initially)
        $wishlists_sql = "CREATE TABLE IF NOT EXISTS wishlists (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($this->conn->query($wishlists_sql) === FALSE) {
            throw new Exception("Error creating wishlists table: " . $this->conn->error);
        }
        
        // Create wishlist_products table
        $wishlist_products_sql = "CREATE TABLE IF NOT EXISTS wishlist_products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            wishlist_id INT NOT NULL,
            product_id INT NOT NULL,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($this->conn->query($wishlist_products_sql) === FALSE) {
            throw new Exception("Error creating wishlist_products table: " . $this->conn->error);
        }
        
        // Add foreign key constraints after both tables exist
        $this->addForeignKeys();
    }
    
    private function addForeignKeys() {
        // Check if foreign key constraints already exist before adding them
        $this->addForeignKeyIfNotExists('wishlists', 'fk_wishlists_user_id', 'user_id', 'users', 'id');
        $this->addForeignKeyIfNotExists('wishlist_products', 'fk_wishlist_products_wishlist_id', 'wishlist_id', 'wishlists', 'id');
        $this->addForeignKeyIfNotExists('wishlist_products', 'fk_wishlist_products_product_id', 'product_id', 'products', 'id');
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

    /**
     * Get or create wishlist for a user
     */
    private function getOrCreateWishlist($userId) {
        // Check if wishlist exists
        $check_sql = "SELECT id FROM wishlists WHERE user_id = ?";
        $stmt = $this->conn->prepare($check_sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['id'];
        } else {
            // Create new wishlist
            $create_sql = "INSERT INTO wishlists (user_id) VALUES (?)";
            $create_stmt = $this->conn->prepare($create_sql);
            $create_stmt->bind_param("i", $userId);
            $create_stmt->execute();
            return $this->conn->insert_id;
        }
    }

    /**
     * Add product to wishlist
     */
    public function addToWishlist($userId, $productId) {
        try {
            $wishlistId = $this->getOrCreateWishlist($userId);
            
            // Check if product is already in wishlist
            if ($this->isProductInWishlist($userId, $productId)) {
                return false; // Already exists
            }
            
            // Add product to wishlist
            $sql = "INSERT INTO wishlist_products (wishlist_id, product_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $wishlistId, $productId);
            
            if ($stmt->execute()) {
                return true;
            } else {
                throw new Exception("Failed to add product to wishlist");
            }
        } catch (Exception $e) {
            throw new Exception("Error adding to wishlist: " . $e->getMessage());
        }
    }

    /**
     * Remove product from wishlist
     */
    public function removeFromWishlist($userId, $productId) {
        try {
            $wishlistId = $this->getOrCreateWishlist($userId);
            
            $sql = "DELETE wp FROM wishlist_products wp 
                    INNER JOIN wishlists w ON wp.wishlist_id = w.id 
                    WHERE w.user_id = ? AND wp.product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $userId, $productId);
            
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Error removing from wishlist: " . $e->getMessage());
        }
    }

    /**
     * Check if product is in user's wishlist
     */
    public function isProductInWishlist($userId, $productId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM wishlist_products wp 
                    INNER JOIN wishlists w ON wp.wishlist_id = w.id 
                    WHERE w.user_id = ? AND wp.product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $userId, $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['count'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get user's wishlist with product details
     */
    public function getWishlist($userId) {
        try {
            $sql = "SELECT p.* FROM products p 
                    INNER JOIN wishlist_products wp ON p.id = wp.product_id 
                    INNER JOIN wishlists w ON wp.wishlist_id = w.id 
                    WHERE w.user_id = ? 
                    ORDER BY wp.added_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $wishlist = [];
            while ($row = $result->fetch_assoc()) {
                $wishlist[] = $row;
            }
            
            return $wishlist;
        } catch (Exception $e) {
            throw new Exception("Error fetching wishlist: " . $e->getMessage());
        }
    }

    /**
     * Get wishlist count for a user
     */
    public function getWishlistCount($userId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM wishlist_products wp 
                    INNER JOIN wishlists w ON wp.wishlist_id = w.id 
                    WHERE w.user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['count'];
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Clear entire wishlist for a user
     */
    public function clearWishlist($userId) {
        try {
            $wishlistId = $this->getOrCreateWishlist($userId);
            
            $sql = "DELETE FROM wishlist_products WHERE wishlist_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $wishlistId);
            
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Error clearing wishlist: " . $e->getMessage());
        }
    }

    /**
     * Toggle wishlist status (add if not exists, remove if exists)
     */
    public function toggleWishlist($userId, $productId) {
        if ($this->isProductInWishlist($userId, $productId)) {
            return $this->removeFromWishlist($userId, $productId);
        } else {
            return $this->addToWishlist($userId, $productId);
        }
    }
}
?>
