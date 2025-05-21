-- 创建商品订购系统数据库
CREATE DATABASE IF NOT EXISTS `ordering_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ordering_system`;

-- 创建用户表
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `phone` varchar(20) NOT NULL COMMENT '手机号',
    `security_code` char(4) NOT NULL COMMENT '4位安全码',
    `name` varchar(50) DEFAULT NULL COMMENT '姓名',
    `gender` enum('男','女','保密') DEFAULT '保密' COMMENT '性别',
    `address` varchar(255) DEFAULT NULL COMMENT '地址',
    `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
    `role` enum('user','admin') NOT NULL DEFAULT 'user' COMMENT '用户角色',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `phone` (`phone`),
    INDEX `idx_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 创建商品表
CREATE TABLE IF NOT EXISTS `products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL COMMENT '商品名称',
    `description` text COMMENT '商品描述',
    `price` decimal(10,2) NOT NULL COMMENT '价格',
    `image` varchar(255) DEFAULT NULL COMMENT '图片URL',
    `category` varchar(50) DEFAULT NULL COMMENT '分类',
    `status` enum('available','unavailable') NOT NULL DEFAULT 'available' COMMENT '状态',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    PRIMARY KEY (`id`),
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 创建订单表
CREATE TABLE IF NOT EXISTS `orders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_number` varchar(30) NOT NULL COMMENT '订单号',
    `user_id` int(11) NOT NULL COMMENT '用户ID',
    `total_amount` decimal(10,2) NOT NULL COMMENT '订单总金额',
    `status` enum('pending','paid','shipped','completed','cancelled') NOT NULL DEFAULT 'pending' COMMENT '订单状态',
    `payment_method` enum('online','offline') DEFAULT 'online' COMMENT '支付方式',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `order_number` (`order_number`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 创建订单商品关联表
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL COMMENT '订单ID',
    `product_id` int(11) NOT NULL COMMENT '商品ID',
    `quantity` int(11) NOT NULL DEFAULT 1 COMMENT '数量',
    `price` decimal(10,2) NOT NULL COMMENT '单价',
    PRIMARY KEY (`id`),
    INDEX `idx_order_id` (`order_id`),
    INDEX `idx_product_id` (`product_id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 插入管理员用户
INSERT INTO `users` (`phone`, `security_code`, `name`, `role`) 
VALUES ('admin', '1234', '管理员', 'admin');

-- 插入示例商品数据
INSERT INTO `products` (`name`, `description`, `price`, `image`, `category`) VALUES
('高级智能手表', '全功能智能手表，支持心率监测、睡眠分析和运动追踪', 1299.00, 'https://picsum.photos/id/160/400/300', '电子产品'),
('便携式咖啡机', '随时随地享受新鲜咖啡，小巧便携，操作简单', 499.00, 'https://picsum.photos/id/96/400/300', '家用电器'),
('无线蓝牙耳机', '高清音质，主动降噪，长达24小时续航', 799.00, 'https://picsum.photos/id/26/400/300', '电子产品'),
('智能温控水杯', '保持水温恒定，智能触控操作，时尚设计', 249.00, 'https://picsum.photos/id/119/400/300', '家居用品');    