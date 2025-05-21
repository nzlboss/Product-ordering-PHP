<?php
/**
 * 系统功能函数库
 */
require_once 'config.php';

/**
 * 用户登录验证
 * @param string $phone 手机号
 * @param string $securityCode 安全码
 * @return array|false 用户数据或false
 */
function authenticateUser($phone, $securityCode) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = :phone AND security_code = :security_code");
    $stmt->execute(['phone' => $phone, 'security_code' => $securityCode]);
    
    return $stmt->fetch();
}

/**
 * 获取用户信息
 * @param int $userId 用户ID
 * @return array|false 用户数据或false
 */
function getUser($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    
    return $stmt->fetch();
}

/**
 * 更新用户信息
 * @param int $userId 用户ID
 * @param array $data 用户数据
 * @return bool 更新成功或失败
 */
function updateUser($userId, $data) {
    global $pdo;
    
    $set = [];
    $params = ['id' => $userId];
    
    foreach ($data as $field => $value) {
        $set[] = "$field = :$field";
        $params[$field] = $value;
    }
    
    if (empty($set)) {
        return false;
    }
    
    $sql = "UPDATE users SET " . implode(', ', $set) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    
    return $stmt->execute($params);
}

/**
 * 修改用户安全码
 * @param int $userId 用户ID
 * @param string $oldSecurityCode 旧安全码
 * @param string $newSecurityCode 新安全码
 * @return bool 修改成功或失败
 */
function changeSecurityCode($userId, $oldSecurityCode, $newSecurityCode) {
    global $pdo;
    
    // 验证旧安全码
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = :id AND security_code = :security_code");
    $stmt->execute(['id' => $userId, 'security_code' => $oldSecurityCode]);
    
    if (!$stmt->fetch()) {
        return false;
    }
    
    // 更新安全码
    $stmt = $pdo->prepare("UPDATE users SET security_code = :new_security_code WHERE id = :id");
    return $stmt->execute(['id' => $userId, 'new_security_code' => $newSecurityCode]);
}

/**
 * 获取商品列表
 * @return array 商品列表
 */
function getProducts() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM products WHERE status = 'available'");
    return $stmt->fetchAll();
}

/**
 * 获取单个商品
 * @param int $productId 商品ID
 * @return array|false 商品数据或false
 */
function getProduct($productId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id AND status = 'available'");
    $stmt->execute(['id' => $productId]);
    
    return $stmt->fetch();
}

/**
 * 创建订单
 * @param int $userId 用户ID
 * @param array $cart 购物车数据
 * @return array|false 订单信息或false
 */
function createOrder($userId, $cart) {
    global $pdo;
    
    if (empty($cart)) {
        return false;
    }
    
    $pdo->beginTransaction();
    
    try {
        // 生成订单号
        $orderNumber = 'OD' . date('YmdHis') . rand(1000, 9999);
        
        // 计算总金额
        $totalAmount = 0;
        foreach ($cart as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }
        
        // 创建订单
        $stmt = $pdo->prepare("
            INSERT INTO orders (order_number, user_id, total_amount, status)
            VALUES (:order_number, :user_id, :total_amount, 'pending')
        ");
        $stmt->execute([
            'order_number' => $orderNumber,
            'user_id' => $userId,
            'total_amount' => $totalAmount
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // 添加订单商品
        foreach ($cart as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (:order_id, :product_id, :quantity, :price)
            ");
            $stmt->execute([
                'order_id' => $orderId,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ]);
        }
        
        $pdo->commit();
        
        return [
            'id' => $orderId,
            'order_number' => $orderNumber,
            'total_amount' => $totalAmount,
            'created_at' => date('Y-m-d H:i:s')
        ];
    } catch (PDOException $e) {
        $pdo->rollBack();
        return false;
    }
}

/**
 * 获取用户订单列表
 * @param int $userId 用户ID
 * @return array 订单列表
 */
function getUserOrders($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT o.* 
        FROM orders o
        WHERE o.user_id = :user_id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute(['user_id' => $userId]);
    
    return $stmt->fetchAll();
}

/**
 * 获取订单详情
 * @param int $orderId 订单ID
 * @return array|false 订单详情或false
 */
function getOrderDetails($orderId) {
    global $pdo;
    
    // 获取订单基本信息
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id");
    $stmt->execute(['id' => $orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        return false;
    }
    
    // 获取订单商品
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name, p.image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = :order_id
    ");
    $stmt->execute(['order_id' => $orderId]);
    $items = $stmt->fetchAll();
    
    $order['items'] = $items;
    return $order;
}

/**
 * 获取所有订单（管理员）
 * @return array 订单列表
 */
function getAllOrders() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT o.*, u.name AS user_name, u.phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ");
    
    return $stmt->fetchAll();
}

/**
 * 获取所有用户（管理员）
 * @return array 用户列表
 */
function getAllUsers() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

/**
 * 更新订单状态（管理员）
 * @param int $orderId 订单ID
 * @param string $status 新状态
 * @return bool 更新成功或失败
 */
function updateOrderStatus($orderId, $status) {
    global $pdo;
    
    $validStatuses = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];
    
    if (!in_array($status, $validStatuses)) {
        return false;
    }
    
    $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
    return $stmt->execute(['id' => $orderId, 'status' => $status]);
}    