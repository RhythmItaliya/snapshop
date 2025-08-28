<?php
class Order {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        // Removed automatic table creation to prevent conflicts
    }
    
    // Create order tables if they don't exist
    public function createTable() {
        // Create orders table first (without foreign keys initially)
        $orders_sql = "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            order_number VARCHAR(50) UNIQUE,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('placed', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'placed',
            payment_method ENUM('razorpay') NOT NULL,
            payment_id VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($this->conn->query($orders_sql) === FALSE) {
            throw new Exception("Error creating orders table: " . $this->conn->error);
        }
        
        // Create order_items table
        $order_items_sql = "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL
        )";
        
        if ($this->conn->query($order_items_sql) === FALSE) {
            throw new Exception("Error creating order_items table: " . $this->conn->error);
        }
        
        // Add foreign key constraints after both tables exist
        $this->addForeignKeys();
    }
    
    private function addForeignKeys() {
        // Check if foreign key constraints already exist before adding them
        $this->addForeignKeyIfNotExists('orders', 'fk_orders_user_id', 'user_id', 'users', 'id');
        $this->addForeignKeyIfNotExists('order_items', 'fk_order_items_order_id', 'order_id', 'orders', 'id');
        $this->addForeignKeyIfNotExists('order_items', 'fk_order_items_product_id', 'product_id', 'products', 'id');
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
    
    // Create new order
    public function createOrder($orderData) {
        $sql = "INSERT INTO orders (user_id, order_number, total_amount, status, payment_method, payment_id, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($sql);
        $orderNumber = 'ORD-' . time() . '-' . $orderData['user_id'];
        
        $stmt->bind_param("isdsis", 
            $orderData['user_id'],
            $orderNumber,
            $orderData['total_amount'],
            $orderData['status'],
            $orderData['payment_method'],
            $orderData['payment_id'] ?? null
        );
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }
    
    // Get order by Razorpay order ID
    public function getOrderByRazorpayId($razorpayOrderId) {
        $sql = "SELECT * FROM orders WHERE payment_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $razorpayOrderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    // Update order
    public function updateOrder($orderId, $updateData) {
        $fields = [];
        $types = "";
        $values = [];
        
        foreach ($updateData as $key => $value) {
            $fields[] = "$key = ?";
            if (is_int($value)) {
                $types .= "i";
            } elseif (is_float($value)) {
                $types .= "d";
            } else {
                $types .= "s";
            }
            $values[] = $value;
        }
        
        $values[] = $orderId;
        $types .= "i";
        
        $sql = "UPDATE orders SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        
        return $stmt->execute();
    }
    
    // Update order with Razorpay order ID
    public function updateOrderRazorpayId($orderId, $razorpayOrderId) {
        $sql = "UPDATE orders SET payment_id = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $razorpayOrderId, $orderId);
        
        return $stmt->execute();
    }
    
    // Get order by ID
    public function getOrderById($orderId) {
        $sql = "SELECT * FROM orders WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    // Get user orders
    public function getUserOrders($userId) {
        $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        return $orders;
    }

    // Get items for an order
    public function getOrderItems($orderId) {
        $sql = "SELECT oi.*, p.name AS product_name FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE order_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    }

    // Get user orders with items and optional status filter
    public function getUserOrdersWithItems($userId, $status = null) {
        $query = "SELECT * FROM orders WHERE user_id = ?";
        if (!empty($status) && $status !== 'all') {
            $query .= " AND status = ?";
        }
        $query .= " ORDER BY created_at DESC";

        if (!empty($status) && $status !== 'all') {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("is", $userId, $status);
        } else {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $userId);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $row['items'] = $this->getOrderItems((int)$row['id']);
            $orders[] = $row;
        }
        return $orders;
    }

    // Cancel order if it belongs to the user and is still cancelable
    public function cancelOrder($orderId, $userId) {
        // Only allow cancel if status is 'placed' or 'processing'
        $sql = "UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status IN ('placed','processing')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $orderId, $userId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}
?>
