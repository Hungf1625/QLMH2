<?php
    require_once '../core/databasePDO.php';
    
    try{
        $query = 'SELECT PD.*, U.fullname,U.role_id, G.groupname
              FROM projectdetail PD
              INNER JOIN users U ON U.id = PD.user_id
              LEFT JOIN `groups` G ON G.group_id = PD.group_id
              WHERE PD.project_id = ?';
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_GET['project_id']]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'projects' => $project
        ]);
    }catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

?>