<?php
header('Content-Type: application/json');
session_start();
require_once '../core/databasePDO.php';
require_once '../core/getUser.php';

try {

    if (!isset($_GET['project_id']) || empty($_GET['project_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu project_id'
        ]);
        exit;
    }

    if (!isset($userInfo) || !is_array($userInfo)) {
        echo json_encode([
            'success' => false,
            'message' => 'Thông tin user không tồn tại'
        ]);
        exit;
    }

    if (isset($userInfo['role_in_group']) && $userInfo['role_in_group'] == 'leader') {

        $query = 'SELECT * FROM projectdetail WHERE project_id = ?';
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_GET['project_id']]);
        $currentPJ = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentPJ) {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy project'
            ]);
            exit;
        }

        if (!empty($currentPJ['group_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Đề tài này đã được đăng ký bởi nhóm khác'
            ]);
            exit;
        }

        $checkGroupQuery = 'SELECT * FROM projectdetail WHERE group_id = ?';
        $checkGroupStmt = $pdo->prepare($checkGroupQuery);
        $checkGroupStmt->execute([$userInfo['group_id']]);
        $existingProject = $checkGroupStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingProject) {
            echo json_encode([
                'success' => false,
                'message' => 'Nhóm của bạn đã đăng ký một đề tài khác'
            ]);
            exit;
        }

        $updateQuery = 'UPDATE projectdetail SET group_id = ?, status = "in_progress" WHERE project_id = ?';
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$userInfo['group_id'], $_GET['project_id']]);

        if ($updateStmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Đăng ký đề tài thành công',
                'groupname' => $userInfo['groupname'],
                'project_id' => $_GET['project_id'],
                'status' => 'in_progress'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Đăng ký thất bại'
            ]);
        }

    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Bạn không phải là nhóm trưởng'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi database: ' . $e->getMessage()
    ]);
}
?>