<?php 
try {
    $user = "SELECT 
                U.*, 
                PD.project_id,
                PD.projectname,
                GM.group_id,
                GM.role_in_group,
                G.groupname
             FROM users U
             LEFT JOIN groupmember GM ON GM.user_id = U.id 
             LEFT JOIN groups G ON G.group_id = GM.group_id
             LEFT JOIN projectdetail PD ON PD.group_id = G.group_id 
             WHERE U.id = ?";
    
    $stmt = $pdo->prepare($user);
    $stmt->execute([$_SESSION['user_id']]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userInfo) {
        echo "User không tồn tại";
        exit;
    }
    
} catch (PDOException $e) {
    echo "Lỗi database: " . $e->getMessage();
    exit;
}
?>