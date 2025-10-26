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
$task_id = $_GET['task_id'] ?? null;



if (!$group_id || !$project_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin nhóm hoặc dự án'
    ]);
    exit;
}

switch($action) {
    case 'newTask':
        newTask($group_id, $project_id);
        break;
    case 'getTasks':
        getTasks($group_id, $project_id);
        break;
    case 'getTaskDetail':
        getTaskDetail($task_id, $project_id, $group_id);
        break;
    case 'delTask':
        delTask($task_id, $project_id, $group_id);
        logTaskActivity($task_id, $project_id, $group_id, 'delTask');
        break;
    case 'uploadFile':
        uploadFile($task_id, $project_id, $group_id);
        logTaskActivity($task_id, $project_id, $group_id, 'uploadFile');
        break;
    case 'submitTask':
        submitTask($task_id, $project_id, $group_id);
        logTaskActivity($task_id, $project_id, $group_id, 'submitTask');
        break;
    case 'getTaskLogs':
        getTaskLogs($project_id, $group_id);
        break;
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Action không hợp lệ'
        ]);
}

function getTasks($group_id, $project_id) {
    global $pdo;
    try {
        
        $selectQuery = "SELECT 
                    T.*,
                    TF.taskfile_id, 
                    TF.filepath, 
                    TF.user_id as file_uploader_id,
                    TF.filename,
                    GM.role_in_group
                FROM tasks T
                LEFT JOIN taskfiles TF ON T.task_id = TF.task_id
                LEFT JOIN groupmember GM ON GM.user_id = ? AND GM.group_id = T.group_id
                WHERE T.group_id = ? AND T.project_id = ? 
                ORDER BY T.created_at DESC";

        $selectStmt = $pdo->prepare($selectQuery);
        $selectStmt->execute([$_SESSION['user_id'], $group_id, $project_id]);
        $tasks = $selectStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($tasks as &$task){
            if(!isset($task['status']) || empty($task['status'])){
                $task['status'] = 'pending'; 
            }
            if($task['taskfile_id'] !== null && $task['status'] === 'pending'){
                $task['status'] = 'submitted';
            }
       
        }
        unset($task); 
        
        echo json_encode([
            'success' => true,
            'tasks' => $tasks ?? []
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy công việc: ' . $e->getMessage()
        ]);
    }
}

function getTaskDetail($task_id, $project_id, $group_id) {
    global $pdo;
    try {
       
        $selectQuery = "SELECT 
                    t.*, 
                    u.fullname as creator_name,
                    gm.role_in_group
                FROM tasks t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN groupmember gm ON gm.user_id = ? AND gm.group_id = t.group_id
                WHERE t.task_id = ? 
                AND t.project_id = ? 
                AND t.group_id = ?";
        $selectStmt = $pdo->prepare($selectQuery);
        $selectStmt->execute([$_SESSION['user_id'], $task_id, $project_id, $group_id]);
        $task = $selectStmt->fetch(PDO::FETCH_ASSOC);

        if (!$task) {
            throw new Exception("Không tìm thấy task");
        }

        
        $fileQuery = "SELECT 
                        tf.*,
                        u.fullname as uploader_name,
                        u.id as uploader_id
                      FROM taskfiles tf
                      LEFT JOIN users u ON tf.user_id = u.id
                      WHERE tf.task_id = ? 
                      AND tf.project_id = ? 
                      AND tf.group_id = ?
                      ORDER BY tf.taskfile_id DESC";
        
        $fileStmt = $pdo->prepare($fileQuery);
        $fileStmt->execute([$task_id, $project_id, $group_id]);
        $files = $fileStmt->fetch(PDO::FETCH_ASSOC);

        $task['files'] = $files;

        echo json_encode([
            'success' => true,
            'task' => $task
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy chi tiết công việc: ' . $e->getMessage()
        ]);
    }
}

function newTask($group_id, $project_id) {
    global $pdo;
    $tasktitle = $_POST['tasktitle'] ?? '';
    $description = $_POST['description'] ?? '';
    $deadline = $_POST['deadline'] ?? '';
    $user_id = $_SESSION['user_id'] ?? '';
    
    if (!$tasktitle || !$deadline) {
        echo json_encode([
            'success' => false,
            'message' => 'Vui lòng điền đầy đủ thông tin'
        ]);
        return;
    }

    try {
   
        $projectQuery = "SELECT deadline FROM projectdetail WHERE project_id = ? AND group_id = ?";
        $projectStmt = $pdo->prepare($projectQuery);
        $projectStmt->execute([$project_id, $group_id]);
        $project = $projectStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy thông tin dự án'
            ]);
            return;
        }
        
        $projectDeadline = $project['deadline'];
        $currentDate = date('Y-m-d H:i:s');
        $taskDeadline = date('Y-m-d H:i:s', strtotime($deadline));
        
  
        if ($taskDeadline < $currentDate) {
            echo json_encode([
                'success' => false,
                'message' => 'Deadline không được nhỏ hơn ngày hiện tại'
            ]);
            return;
        }
        
  
        if ($projectDeadline && $taskDeadline > $projectDeadline) {
            echo json_encode([
                'success' => false,
                'message' => 'Deadline của task không được vượt quá hạn chót của dự án ('.date('d/m/Y', strtotime($projectDeadline)).')'
            ]);
            return;
        }
        
        $insertQuery = "INSERT INTO tasks (tasktitle, description, deadline, group_id, project_id, user_id, status, created_at) 
                       VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([$tasktitle, $description, $taskDeadline, $group_id, $project_id, $user_id]);

        $task_id = $pdo->lastInsertId();
        
        logTaskActivity($task_id, $project_id, $group_id, 'newTask');
        
        // ECHO RESPONSE SAU CÙNG
        echo json_encode([
            'success' => true,
            'message' => 'Tạo công việc thành công',
            'task_id' => $task_id  // Thêm task_id vào response
        ]);
        
        return $task_id;
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi tạo công việc: ' . $e->getMessage()
        ]);
        return false;
    }
}

function delTask($task_id, $project_id, $group_id){
    global $pdo;
    try {
 
        $pdo->beginTransaction();

        $checkQuery = "SELECT task_id FROM tasks WHERE task_id = ? AND project_id = ? AND group_id = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$task_id, $project_id, $group_id]);

        $deleteQuery1 = "DELETE FROM taskfiles WHERE task_id = ? AND project_id = ? AND group_id = ?";
        $deleteStmt1 = $pdo->prepare($deleteQuery1);
        $deleteStmt1->execute([$task_id, $project_id, $group_id]);

        $deleteQuery2 = "DELETE FROM tasks WHERE task_id = ? AND project_id = ? AND group_id = ?";
        $deleteStmt2 = $pdo->prepare($deleteQuery2);
        $deleteStmt2->execute([$task_id, $project_id, $group_id]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Xóa công việc thành công',
            'deleted_task_id' => $task_id
        ]);

    } catch (Exception $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi xóa công việc: ' . $e->getMessage()
        ]);
    }
}

function uploadFile($task_id, $project_id, $group_id){
    global $pdo;
    header('Content-Type: application/json');

    $uploadDir = '../uploads/';
    $maxFileSize = 30 * 1024 * 1024;
    $allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    try {

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Lỗi upload file. Mã lỗi: ' . $_FILES['file']['error']);
        }

        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $description = $_POST['description'] ?? '';
        $user_id = $_SESSION['user_id'] ?? ''; 

        if ($fileSize > $maxFileSize) {
            throw new Exception('File quá lớn. Kích thước tối đa: 30MB');
        }

        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Loại file không được hỗ trợ. Chấp nhận: ' . implode(', ', $allowedTypes));
        }

        $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        $uploadPath = $uploadDir . $newFileName;

        if (!move_uploaded_file($fileTmpName, $uploadPath)) {
            throw new Exception('Lỗi khi lưu file');
        }

        // Bắt đầu transaction để đảm bảo tính nhất quán
        $pdo->beginTransaction();

        // 1. Insert file vào bảng taskfiles
        $insertQuery = "INSERT INTO taskfiles (
            task_id, 
            project_id, 
            group_id,
            user_id,
            filename, 
            filepath,
            filesize,
            filetype,
            description
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([
            $task_id,
            $project_id,
            $group_id,
            $user_id,
            $newFileName,     
            $uploadPath,       
            $fileSize,         
            $fileType,         
            $description       
        ]);

        $file_id = $pdo->lastInsertId();

        // 2. CẬP NHẬT STATUS CỦA TASK THÀNH 'submitted'
        $updateTaskQuery = "UPDATE tasks SET status = ? WHERE task_id = ? AND project_id = ? AND group_id = ?";
        $updateTaskStmt = $pdo->prepare($updateTaskQuery);
        $updateTaskStmt->execute(['submitted', $task_id, $project_id, $group_id]);

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Upload file thành công!',
            'file_id' => $file_id,
            'filename' => $newFileName,
            'task_updated' => true,
            'new_status' => 'submitted'
        ]);

    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function submitTask($task_id, $project_id, $group_id){
    global $pdo;
    
    try {
        $query1 = 'SELECT * FROM tasks WHERE task_id = ? AND project_id = ? AND group_id = ?';
        $stmt1 = $pdo->prepare($query1);
        $stmt1->execute([$task_id, $project_id, $group_id]);
        $currentTask = $stmt1->fetch(PDO::FETCH_ASSOC);

        if (!$currentTask) {
            throw new Exception('Không tìm thấy công việc');
        }
        if ($currentTask['status'] === 'submitted') {
            $updateQuery = 'UPDATE tasks SET status = ? WHERE task_id = ? AND project_id = ? AND group_id = ?';
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute(['completed', $task_id, $project_id, $group_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Xác nhận hoàn thành công việc thành công!'
            ]);
            
        } else if ($currentTask['status'] === 'pending' || $currentTask['status'] === 'completed') {
            $message = '';
            if ($currentTask['status'] === 'pending') {
                $message = 'Công việc chưa được nộp file. Không thể xác nhận hoàn thành.';
            } else if ($currentTask['status'] === 'completed') {
                $message = 'Công việc đã được hoàn thành trước đó.';
            }
            
            throw new Exception($message);
            
        } else {
            throw new Exception('Trạng thái công việc không hợp lệ: ' . $currentTask['status']);
        }

    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function logTaskActivity($task_id, $project_id, $group_id, $action) {
    global $pdo;
    
    try {
        require_once '../core/databasePDO.php';
        require_once '../core/getUser.php'; // File này đã trả về $userInfo
        
        // File getUser.php đã có $userInfo, nhưng cần kiểm tra lại
        if (!isset($userInfo) || empty($userInfo)) {
            // Fallback: query trực tiếp nếu cần
            $userQuery = "SELECT * FROM users WHERE id = ?";
            $userStmt = $pdo->prepare($userQuery);
            $userStmt->execute([$_SESSION['user_id']]);
            $userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);
        }
        
        $user_id = $userInfo['id'] ?? $_SESSION['user_id'] ?? null;
        $fullname = $userInfo['fullname'] ?? 'Unknown User';
        
        // Tự động tạo description dựa trên action
        $actionMessages = [
            'newTask' => 'đã tạo công việc mới',
            'update' => 'đã cập nhật công việc', 
            'delTask' => 'đã xóa công việc',
            'submitTask' => 'đã xác nhận hoàn thành công việc',
            'uploadFile' => 'đã nộp file cho công việc'
        ];
        
        $message = $actionMessages[$action] ?? 'đã thực hiện thao tác trên công việc';
        $description = $fullname . ' ' . $message . ' (ID: ' . $task_id . ')';
        
        $query = 'INSERT INTO task_activities 
                  (task_id, project_id, group_id, user_id, action, description, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())';
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$task_id, $project_id, $group_id, $user_id, $action, $description]);
        
        return true;
        
    } catch(Exception $e) {
        error_log('Lỗi khi ghi log: ' . $e->getMessage());
        return false;
    }
}

function getTaskLogs($project_id, $group_id) {
    global $pdo;
    try {
        $query = 'SELECT * FROM task_activities WHERE project_id = ? AND group_id = ? ORDER BY created_at DESC';
        $stmt = $pdo->prepare($query);
        $stmt->execute([$project_id, $group_id]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'logs' => $logs  
        ]);
        
    } catch(Exception $e) {

        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy logs: ' . $e->getMessage()
        ]);
    }
}