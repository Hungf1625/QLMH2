<?php
// Kết nối database
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=qlmh;charset=utf8mb4",
        "root", 
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi kết nối cơ sở dữ liệu',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

?>