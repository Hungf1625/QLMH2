<?php
session_start();
header('Content-Type: application/json');
require_once '../core/databasePDO.php';

try {

    $project_id = $_GET['project_id'] ?? null;
    
    if(!$project_id) {
        throw new Exception('Đề tài không tồn tại');
    }

    // Kiểm tra xem project có tồn tại và thuộc về group này không
    $checkQuery = 'SELECT * FROM projectdetail WHERE project_id = ?';
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([$project_id]);
    $project = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if(!$project) {
        throw new Exception('Không tìm thấy đề tài hoặc đề tài không thuộc về nhóm này');
    }

    // XÓA HOÀN TOÀN RECORD thay vì update
    $deleteQuery = 'DELETE FROM projectdetail WHERE project_id = ? AND user_id = ?';
    $deleteStmt = $pdo->prepare($deleteQuery);
    $deleteStmt->execute([$project_id,$_SESSION['user_id']]);

    if($deleteStmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Đã xóa đề tài thành công'
        ]);
    } else {
        throw new Exception('Xóa đề tài thất bại (không phải là giảng viên ra đề)');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>