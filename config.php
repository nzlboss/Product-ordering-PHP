<?php
/**
 * 数据库配置文件
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'ordering_system');
define('DB_USER', 'root');
define('DB_PASSWORD', 'your_password');
define('DB_CHARSET', 'utf8mb4');

// 创建数据库连接
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}    