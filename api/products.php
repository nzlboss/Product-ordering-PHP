<?php
/**
 * 商品API接口
 */
require_once '../functions.php';

// 设置响应头
header('Content-Type: application/json');

// 获取商品列表
$products = getProducts();

echo json_encode([
    'success' => true,
    'data' => $products
]);    