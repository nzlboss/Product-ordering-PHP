<?php
/**
 * 管理员订单API接口
 */
require_once '../../functions.php';

// 设置响应头
header('Content-Type: application/json');

// 检查用户是否登录且为管理员
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => '权限不足'
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // 获取所有订单
        $orders = getAllOrders();
        
        echo json_encode([
            'success' => true,
            'data' => $orders
        ]);
        break;
    
    case 'PUT':
        // 更新订单状态
        $data = json_decode(file_get_contents('php://input'), true);
        
        $orderId = $data['order_id'] ?? 0;
        $status = $data['status'] ?? '';
        
        if (empty($orderId) || empty($status)) {
            echo json_encode([
                'success' => false,
                'message' => '参数不足'
            ]);
            exit;
        }
        
        if (updateOrderStatus($orderId, $status)) {
            echo json_encode([
                'success' => true,
                'message' => '订单状态已更新'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '更新订单状态失败'
            ]);
        }
        break;
    
    default:
        header('HTTP/1.1 405 Method Not Allowed');
        echo json_encode([
            'success' => false,
            'message' => 'Method Not Allowed'
        ]);
}    