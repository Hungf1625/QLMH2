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
// $task_id = $_GET['task_id'] ?? null;
if(isset($_GET['role_id'])){
    $role_id = $_GET['role_id'];
}


switch($action){
    case 'getProjects':
        getProjects($role_id);
        break;
    case 'accProject':
        accProject($role_id,$project_id,$group_id);
        break;
    case 'cancelProject':
        cancelProject($role_id,$project_id,$group_id);
        break;
    case 'getProjectInfor':
        getProjectInfor($project_id,$group_id);
        break;
    case 'getCompletedTasks':
        getCompletedTasks($project_id,$group_id);
        break;
    case 'getGroupMembers':
        getGroupMembers($group_id);
        break;
    case 'insertResult':
        insertResult($project_id,$group_id);
        break;
}

function getProjects($role_id) {
    global $pdo;
    try {
        $query = 'SELECT 
                    PD.project_id,
                    PD.group_id,
                    PD.projectname,
                    PD.deadline,
                    PD.status as project_status,
                    PD.re_evaluation,
                    RE.title as revaluation_title,
                    RE.description as revaluation_description,
                    RE.request_date,
                    RE.status as revaluation_status,
                    RE.id as revaluation_id,
                    U.username as lecturer_username,      
                    U.fullname as lecturer_fullname,      
                    U.email as lecturer_email,           
                    U.role_id as lecturer_role             
                  FROM projectdetail PD 
                  INNER JOIN reevalutiondetail RE 
                    ON RE.project_id = PD.project_id 
                   AND RE.group_id = PD.group_id
                  INNER JOIN users U
                    ON PD.user_id = U.id
                  WHERE RE.status = ?';
        
        $status = '';
        if($role_id === "GV") {
            $status = "pending";
        } else if($role_id === "TK") {
            $status = "in_progress";
        }else if($role_id === "HD"){
            $status = "sent";
        }else {
            echo json_encode([
                'success' => false,
                'message' => 'Role không hợp lệ'
            ]);
            return;
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$status]);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'projects' => $projects,
            'count' => count($projects),
            'role' => $role_id,
            'status_filter' => $status
        ]);
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi hệ thống: ' . $e->getMessage()
        ]);
    }
}

function accProject($role_id,$project_id,$group_id){
    global $userInfo;
    global $pdo;
    if($role_id === "GV"){
        $query1 = "UPDATE reevalutiondetail SET lecturer_id = ?, status = 'in_progress' WHERE project_id = ? AND group_id = ? AND status = 'pending'";
        $stmt1 = $pdo->prepare($query1);
        $stmt1->execute([$userInfo['id'], $project_id, $group_id]);
            
        $query2 = "UPDATE projectdetail SET re_evaluation = 'in_progress' WHERE project_id = ? AND group_id = ?";
        $stmt2 = $pdo->prepare($query2);
        $stmt2->execute([$project_id, $group_id]);
        
        if ($stmt1->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Đã chuyển yêu cầu phúc khảo sang trạng thái xử lý'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu phúc khảo để cập nhật hoặc yêu cầu đã được xử lý'
            ]);
            }
    
    }else if($role_id === "TK"){
            $query1 = "UPDATE reevalutiondetail SET secretary_id = ?, status = 'sent' WHERE project_id = ? AND group_id = ? AND status = 'in_progress'";
            $stmt1 = $pdo->prepare($query1);
            $stmt1->execute([$userInfo['id'], $project_id, $group_id]);

            if ($stmt1->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã phê duyệt yêu cầu phúc khảo'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy yêu cầu phúc khảo để cập nhật hoặc yêu cầu đã được xử lý'
                ]);
            }
                }
}

function cancelProject($role_id,$project_id,$group_id){
    global $pdo;
    global $userInfo;
    try{
        if($role_id === "GV"){
            $query1 = "UPDATE reevalutiondetail SET lecturer_id = ?, status = 'rejected' WHERE project_id = ? AND group_id = ? AND status = 'pending'";
            $stmt1 = $pdo->prepare($query1);
            $stmt1->execute([$userInfo['id'],$project_id, $group_id]);
                
            $query2 = "UPDATE projectdetail SET re_evaluation = NULL WHERE project_id = ? AND group_id = ?";
            $stmt2 = $pdo->prepare($query2);
            $stmt2->execute([$project_id, $group_id]);
            
            if ($stmt1->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã chuyển yêu cầu phúc khảo sang trạng thái xử lý'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy yêu cầu phúc khảo để cập nhật hoặc yêu cầu đã được xử lý'
                ]);
            }
        }else if($role_id ==="TK"){
            $query1 = "UPDATE reevalutiondetail SET secretary_id = ?, status = 'rejected' WHERE project_id = ? AND group_id = ? AND status = 'in_progress'";
            $stmt1 = $pdo->prepare($query1);
            $stmt1->execute([$userInfo['id'],$project_id, $group_id]);


            if ($stmt1->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã xóa yêu cầu phúc khảo'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy yêu cầu phúc khảo để cập nhật hoặc yêu cầu đã được xử lý'
                ]);
            }
        }
    }catch(Exception $e){
        throw new Exception($e->getMessage());
    }
}

function getProjectInfor($project_id, $group_id) {
    global $pdo;
    try {
        $query = 'SELECT 
            PD.project_id, 
            PD.group_id,
            PD.projectname, 
            PD.deadline, 
            PD.description,
            PD.created_at,
            PD.status,
            PD.submitted_at,
            PD.result,
            PD.re_evaluation,
            PD.user_id as lecturer_id,
            RE.title as RE_title,
            RE.description as RE_description,
            RE.request_date as RE_date,
            RE.status as RE_status,
            U.fullname as lecturer_name,
            U.email as lecturer_email
          FROM projectdetail PD
          LEFT JOIN users U ON PD.user_id = U.id
          LEFT JOIN reevalutiondetail RE ON RE.project_id = PD.project_id AND RE.group_id = PD.group_id
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
                'message' => 'Không tìm thấy project với project_id: ' . $project_id . ' và group_id: ' . $group_id
            ]);
        }
        
    } catch(Exception $e) {
        error_log("Lỗi getProjectInfor: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi hệ thống khi lấy thông tin project'
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

function insertResult($project_id, $group_id){
    global $userInfo;
    global $pdo;
    
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        echo json_encode([
            'success' => false,
            'message' => 'Phương thức không hợp lệ'
        ]);
        return;
    }
    
    $result = trim($_POST['result'] ?? '');
    
    if (empty($result)) {
        echo json_encode([
            'success' => false,
            'message' => 'Kết quả không được để trống'
        ]);
        return;
    }
    
    try {
        $query = 'UPDATE projectdetail SET result = ?, re_evaluation = "completed" WHERE project_id = ? AND group_id = ?';
        $stmt = $pdo->prepare($query);
        $stmt->execute([$result, $project_id, $group_id]);

        $query2 = 'UPDATE reevalutiondetail SET council_id = ?, result = ?, status = "completed" WHERE project_id = ? AND group_id = ?';
        $stmt2 = $pdo->prepare($query2);
        $stmt2->execute([$userInfo['id'], $result, $project_id, $group_id]); 
        
        if ($stmt->rowCount() > 0 || $stmt2->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật kết quả và hoàn thành phúc khảo thành công',
                'result' => $result,
                'council_id' => $userInfo['id']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy project để cập nhật kết quả'
            ]);
        }
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi hệ thống: ' . $e->getMessage()
        ]);
    }
}

?>