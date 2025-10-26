<?php
session_start();
header('Content-Type: application/json');
require_once '../core/databasePDO.php';
require_once '../core/getUser.php';

if (!isset($userInfo)) {
    echo json_encode([
        'success' => false,
        'message' => 'Người dùng chưa đăng nhập'
    ]);
    exit;
}

$action = $_GET['action'] ?? '';
$group_id = $_GET['group_id'] ?? null;
$project_id = $_GET['project_id'] ?? null;


if (!$group_id || !$project_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin nhóm hoặc dự án'
    ]);
    exit;
}


switch($action){
    case 'getProjectInfor':
        getProjectInfor($project_id, $group_id);
        break;
    case 'getGroupMembers':
        getGroupMembers($group_id);
        break;
    case 'getCompletedTasks':
        getCompletedTasks($project_id, $group_id);
        break;
    case 'submitProject':
        submitProject($project_id, $group_id);
        break;
    case 'cancelSubmitPJ':
        cancelSubmitPJ($project_id, $group_id);
        break;
    case 'reEvalutionBtn':
        reEvalutionBtn($project_id, $group_id);
        break;
}

function getProjectInfor($project_id, $group_id) {
    global $pdo;
    try {
        $query = 'SELECT 
                    PD.project_id, 
                    PD.projectname, 
                    PD.deadline, 
                    PD.description,
                    PD.created_at,
                    PD.status,
                    PD.result,
                    PD.re_evaluation,
                    U.fullname as lecturer_name
                  FROM projectdetail PD
                  INNER JOIN users U ON PD.user_id = U.id
                  WHERE PD.project_id = ? AND PD.group_id = ?';
                  
        $stmt = $pdo->prepare($query);
        $stmt->execute([$project_id, $group_id]);
        $projectInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($projectInfo) {
            echo json_encode([
                'success' => true,
                'project' => $projectInfo
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy project'
            ]);
        }
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ]);
    }
}

function getGroupMembers($group_id) {
    global $pdo;
    try {
        $query = 'SELECT 
                    U.studentID as MSSV,
                    U.fullname,
                    GM.role_in_group,
                    GM.joined_at
                  FROM groupmember GM
                  INNER JOIN users U ON GM.user_id = U.id
                  WHERE GM.group_id = ?
                  ORDER BY 
                    CASE GM.role_in_group 
                        WHEN "leader" THEN 1
                        WHEN "member" THEN 2
                        ELSE 3
                    END,
                    U.fullname';
                  
        $stmt = $pdo->prepare($query);
        $stmt->execute([$group_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($members) {
            echo json_encode([
                'success' => true,
                'members' => $members,
                'count' => count($members)
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không có thành viên trong nhóm'
            ]);
        }
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy danh sách thành viên: ' . $e->getMessage()
        ]);
    }
}

function getCompletedTasks($project_id, $group_id){
    global $pdo;
    try {
        $query = 'SELECT 
                    T.*,
                    TF.user_id,
                    TF.filename,
                    TF.filepath,
                    TF.filetype,
                    U.fullname as uploader_name
                 FROM tasks T 
                 INNER JOIN taskfiles TF ON T.task_id = TF.task_id
                 INNER JOIN users U ON TF.user_id = U.id
                 WHERE T.status = "completed" 
                 AND T.project_id = ? 
                 AND T.group_id = ?
                 ORDER BY T.created_at DESC';
                 
        $stmt = $pdo->prepare($query);
        $stmt->execute([$project_id, $group_id]);
        $completedTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($completedTasks) {
            echo json_encode([
                'success' => true,
                'tasks' => $completedTasks,
                'count' => count($completedTasks)
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'tasks' => [],
                'count' => 0,
                'message' => 'Không có công việc đã hoàn thành'
            ]);
        }
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy công việc đã hoàn thành: ' . $e->getMessage()
        ]);
    }  
}

function submitProject($project_id, $group_id){
    global $pdo;
    try {
        $checkQuery = 'SELECT project_id FROM projectdetail WHERE project_id = ? AND group_id = ?';
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$project_id, $group_id]);
        $project = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$project) {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy đề tài hoặc đề tài không thuộc về nhóm của bạn'
            ]);
            return;
        }

        $updateQuery = 'UPDATE projectdetail SET status = "submitted", submitted_at = NOW() WHERE project_id = ? AND group_id = ?';
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$project_id, $group_id]);
        
        if ($updateStmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Nộp đề tài thành công',
                'project_id' => $project_id,
                'status' => 'submitted'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể cập nhật trạng thái đề tài'
            ]);
        }
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi nộp đề tài: ' . $e->getMessage()
        ]);
    }
}

function cancelSubmitPJ($project_id, $group_id){
    global $pdo;
    try {
        $checkQuery = 'SELECT project_id, status FROM projectdetail WHERE project_id = ? AND group_id = ?';
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$project_id, $group_id]);
        $project = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$project) {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy đề tài hoặc đề tài không thuộc về nhóm của bạn'
            ]);
            return;
        }

        if($project['status'] !== 'submitted') {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể hủy nộp bài vì đề tài chưa được nộp'
            ]);
            return;
        }

        $updateQuery = 'UPDATE projectdetail SET status = "in_progress" WHERE project_id = ? AND group_id = ?';
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$project_id, $group_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Đã hủy nộp bài thành công',
            'project_id' => $project_id,
            'status' => 'in_progress'
        ]);
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ]);
    }
}

function reEvalutionBtn($project_id, $group_id){
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        global $pdo;
        
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);

        try {
            $checkQuery = 'SELECT project_id, status FROM projectdetail WHERE project_id = ? AND group_id = ?';
            $checkStmt = $pdo->prepare($checkQuery);
            $checkStmt->execute([$project_id, $group_id]);
            $project = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$project) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy project hoặc không có quyền'
                ]);
                return;
            }
            
            if ($project['status'] !== 'rejected' && $project['status'] !== 'approved') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Chỉ có thể gửi yêu cầu đánh giá lại khi project bị từ chối hoặc muốn xét lại điểm'
                ]);
                return;
            }

            $checkReEvalQuery = 'SELECT re_evaluation FROM projectdetail WHERE project_id = ? AND group_id = ?';
            $checkReEvalStmt = $pdo->prepare($checkReEvalQuery);
            $checkReEvalStmt->execute([$project_id, $group_id]);
            $currentStatus = $checkReEvalStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($currentStatus && ($currentStatus['re_evaluation'] === 'pending' || $currentStatus['re_evaluation'] === 'in_progress')) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Đã có yêu cầu phúc khảo đang được xử lý'
                ]);
                return;
            }

            $insertReEvalQuery = 'INSERT INTO reevalutiondetail (title, description, project_id, group_id, request_date, status, lecturer_id ,secretary_id ,council_id) 
                                VALUES (?, ?, ?, ?, NOW(), "pending",NULL,NULL,NULL)';
            $insertStmt = $pdo->prepare($insertReEvalQuery);
            $insertStmt->execute([$title, $description, $project_id, $group_id]);

            $updateQuery = 'UPDATE projectdetail SET re_evaluation = "pending" WHERE project_id = ? AND group_id = ?';
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute([$project_id, $group_id]);
            
            if ($updateStmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã gửi yêu cầu đánh giá lại thành công',
                    'project_id' => $project_id,
                    're_evaluation_status' => 'pending'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gửi yêu cầu đánh giá lại thất bại'
                ]);
            }
            
        } catch(Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ]);
        }
    }
}

?>