<?php
session_start();
header('Content-Type: application/json'); 

require_once '../core/databasePDO.php';

$action = $_GET['action'] ?? '';

switch($action){
    case 'deleteProject':
        deleteProject();
        break;
    case 'deleteGroup':
        deleteGroup();
        break;
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Action không hợp lệ'
        ]);
        exit;
}

function deleteProject(){
    global $pdo;
    
    try {
        $project_id = $_GET['project_id'] ?? null;
        
        if(!$project_id) {
            throw new Exception('Đề tài không tồn tại');
        }

        $checkQuery = 'SELECT * FROM projectdetail WHERE project_id = ?';
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$project_id]);
        $project = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if(!$project) {
            throw new Exception('Không tìm thấy đề tài');
        }

        $deleteQuery = 'UPDATE projectdetail SET status = "canceled", group_id = "none" WHERE project_id = ? AND user_id = ?';
        $deleteStmt = $pdo->prepare($deleteQuery);
        $deleteStmt->execute([$project_id, $_SESSION['user_id']]);

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
}

function deleteGroup(){
    global $pdo; 
    try {
        $project_id = $_GET['project_id'] ?? null;
        
        if(!$project_id) {
            throw new Exception('Đề tài không tồn tại');
        }

        $checkQuery = 'SELECT * FROM projectdetail WHERE project_id = ?';
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$project_id]);
        $project = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if(!$project) {
            throw new Exception('Không tìm thấy đề tài');
        }

        $updateQuery = 'UPDATE projectdetail SET group_id = NULL, status = "pending" WHERE project_id = ?';
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$project_id]);

        if($updateStmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Đã hủy nhóm đăng ký đề tài thành công',
                'status' => 'pending'
            ]);
        } else {
            throw new Exception('Hủy đăng ký thất bại (bạn không có quyền hoặc đề tài không tồn tại)');
        }

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>