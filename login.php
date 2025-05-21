<?php
/**
 * 登录处理脚本
 */
require_once 'functions.php';

// 检查是否为POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo 'Method Not Allowed';
    exit;
}

// 获取表单数据
$phone = $_POST['phone'] ?? '';
$securityCode = $_POST['security_code'] ?? '';

// 验证数据
if (empty($phone) || empty($securityCode)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '请输入手机号和安全码'
    ]);
    exit;
}

// 验证用户
$user = authenticateUser($phone, $securityCode);

if (!$user) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '手机号或安全码错误'
    ]);
    exit;
}

// 开始会话
session_start();

// 存储用户信息到会话
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];
$_SESSION['phone'] = $user['phone'];

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => '登录成功',
    'role' => $user['role']
]);    