<?php
/**
 * 订单API接口
 */
require_once '../functions.php';

// 设置响应头
header('Content-Type: application/json');

// 检查用户是否登录
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => '请先登录'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // 获取用户订单列表
        $orders = getUserOrders($userId);
        
        echo json_encode([
            'success' => true,
            'data' => $orders
        ]);
        break;
    
    case 'POST':
        // 创建订单
        $cart = json_decode(file_get_contents('php://input'), true);
        
        if (empty($cart)) {
            echo json_encode([
                'success' => false,
                'message' => '购物车不能为空'
            ]);
            exit;
        }
        
        $order = createOrder($userId, $cart);
        
        if ($order) {
            echo json_encode([
                'success' => true,
                'data' => $order
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '创建订单失败'
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