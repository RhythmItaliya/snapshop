<?php
// Orders API: GET /api/orders.php?status=all|placed|... and POST /api/orders.php?action=cancel&id=ORDER_ID
session_start();

// If accessed directly from the browser (expecting HTML), redirect to orders page
$accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
$requestedWith = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) : '';
if (stripos($accept, 'text/html') !== false && stripos($accept, 'application/json') === false && $requestedWith !== 'xmlhttprequest') {
    header('Location: /snapshop/orders.php');
    exit;
}

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../modal/order.model.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([ 'success' => false, 'message' => 'Unauthorized' ]);
    exit;
}

try {
    $conn = getDatabaseConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $orderModel = new Order($conn);
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $status = isset($_GET['status']) ? $_GET['status'] : 'all';
        $orders = $orderModel->getUserOrdersWithItems($_SESSION['user_id'], $status);
        echo json_encode([ 'success' => true, 'orders' => $orders ]);
    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'cancel') {
            $orderId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($orderId <= 0) {
                http_response_code(400);
                echo json_encode([ 'success' => false, 'message' => 'Invalid order id' ]);
            } else {
                $ok = $orderModel->cancelOrder($orderId, $_SESSION['user_id']);
                if ($ok) {
                    echo json_encode([ 'success' => true ]);
                } else {
                    http_response_code(400);
                    echo json_encode([ 'success' => false, 'message' => 'Cannot cancel this order' ]);
                }
            }
        } else {
            http_response_code(400);
            echo json_encode([ 'success' => false, 'message' => 'Invalid action' ]);
        }
    } else {
        http_response_code(405);
        echo json_encode([ 'success' => false, 'message' => 'Method not allowed' ]);
    }

    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([ 'success' => false, 'message' => $e->getMessage() ]);
}
