<?php
class Cart {
    private $conn;
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
        $this->createTable();
    }
    
    private function createTable() {
        // Create carts table first (without foreign keys initially)
        $carts_sql = "CREATE TABLE IF NOT EXISTS carts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($this->conn->query($carts_sql) === FALSE) {
            throw new Exception("Error creating carts table: " . $this->conn->error);
        }
        
        // Create cart_items table
        $cart_items_sql = "CREATE TABLE IF NOT EXISTS cart_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cart_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            price DECIMAL(10,2) NOT NULL,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($this->conn->query($cart_items_sql) === FALSE) {
            throw new Exception("Error creating cart_items table: " . $this->conn->error);
        }
        
        // Add foreign key constraints after both tables exist
        $this->addForeignKeys();
    }
    
    private function addForeignKeys() {
        // Check if foreign key constraints already exist before adding them
        $this->addForeignKeyIfNotExists('carts', 'fk_carts_user_id', 'user_id', 'users', 'id');
        $this->addForeignKeyIfNotExists('cart_items', 'fk_cart_items_cart_id', 'cart_id', 'carts', 'id');
        $this->addForeignKeyIfNotExists('cart_items', 'fk_cart_items_product_id', 'product_id', 'products', 'id');
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
     * Get or create cart for a user
     */
    private function getOrCreateCart($userId) {
        // Check if cart exists
        $check_sql = "SELECT id FROM carts WHERE user_id = ?";
        $stmt = $this->conn->prepare($check_sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['id'];
        } else {
            // Create new cart
            $create_sql = "INSERT INTO carts (user_id) VALUES (?)";
            $create_stmt = $this->conn->prepare($create_sql);
            $create_stmt->bind_param("i", $userId);
            $create_stmt->execute();
            return $this->conn->insert_id;
        }
    }

    /**
     * Add product to cart
     */
    public function addToCart($userId, $productId, $quantity = 1, $price = 0) {
        try {
            $cartId = $this->getOrCreateCart($userId);
            
            // Check if product is already in cart
            $existingItem = $this->getCartItem($userId, $productId);
            
            if ($existingItem) {
                // Update quantity if product already exists
                $newQuantity = $existingItem['quantity'] + $quantity;
                return $this->updateCartItemQuantity($userId, $productId, $newQuantity);
            } else {
                // Add new product to cart
                $sql = "INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("iiid", $cartId, $productId, $quantity, $price);
                
                if ($stmt->execute()) {
                    return true;
                } else {
                    throw new Exception("Failed to add product to cart");
                }
            }
        } catch (Exception $e) {
            throw new Exception("Error adding to cart: " . $e->getMessage());
        }
    }

    /**
     * Get cart item for a specific product
     */
    public function getCartItem($userId, $productId) {
        try {
            $sql = "SELECT ci.* FROM cart_items ci 
                    INNER JOIN carts c ON ci.cart_id = c.id 
                    WHERE c.user_id = ? AND ci.product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $userId, $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItemQuantity($userId, $productId, $quantity) {
        try {
            $cartId = $this->getOrCreateCart($userId);
            
            if ($quantity <= 0) {
                // Remove item if quantity is 0 or less
                return $this->removeFromCart($userId, $productId);
            }
            
            $sql = "UPDATE cart_items ci 
                    INNER JOIN carts c ON ci.cart_id = c.id 
                    SET ci.quantity = ? 
                    WHERE c.user_id = ? AND ci.product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iii", $quantity, $userId, $productId);
            
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Error updating cart item: " . $e->getMessage());
        }
    }

    /**
     * Remove product from cart
     */
    public function removeFromCart($userId, $productId) {
        try {
            $sql = "DELETE ci FROM cart_items ci 
                    INNER JOIN carts c ON ci.cart_id = c.id 
                    WHERE c.user_id = ? AND ci.product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $userId, $productId);
            
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Error removing from cart: " . $e->getMessage());
        }
    }

    /**
     * Get user's cart items with product details
     */
    public function getCartItems($userId) {
        try {
            $sql = "SELECT ci.*, p.name, p.image, p.price as product_price 
                    FROM cart_items ci 
                    INNER JOIN carts c ON ci.cart_id = c.id 
                    INNER JOIN products p ON ci.product_id = p.id 
                    WHERE c.user_id = ? 
                    ORDER BY ci.added_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $cartItems = [];
            while ($row = $result->fetch_assoc()) {
                // Format the data to match the expected structure
                $cartItems[] = [
                    'product_id' => $row['product_id'],
                    'quantity' => $row['quantity'],
                    'price' => $row['price'],
                    'product' => [
                        'id' => $row['product_id'],
                        'name' => $row['name'],
                        'image' => $row['image'],
                        'price' => $row['product_price']
                    ]
                ];
            }
            
            return $cartItems;
        } catch (Exception $e) {
            throw new Exception("Error fetching cart items: " . $e->getMessage());
        }
    }

    /**
     * Get cart total for a user
     */
    public function getCartTotal($userId) {
        try {
            $sql = "SELECT SUM(ci.price * ci.quantity) as total FROM cart_items ci 
                    INNER JOIN carts c ON ci.cart_id = c.id 
                    WHERE c.user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get cart item count for a user
     */
    public function getCartItemCount($userId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM cart_items ci 
                    INNER JOIN carts c ON ci.cart_id = c.id 
                    WHERE c.user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Clear entire cart for a user
     */
    public function clearCart($userId) {
        try {
            $cartId = $this->getOrCreateCart($userId);
            
            $sql = "DELETE FROM cart_items WHERE cart_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $cartId);
            
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Error clearing cart: " . $e->getMessage());
        }
    }

    /**
     * Check if product is in user's cart
     */
    public function isProductInCart($userId, $productId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM cart_items ci 
                    INNER JOIN carts c ON ci.cart_id = c.id 
                    WHERE c.user_id = ? AND ci.product_id = ?";
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
}
?>
