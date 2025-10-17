<?php

header('Content-Type: application/json');
session_start();
require_once '../core/databasePDO.php';

try {
    $query = "SELECT PD.*, U.fullname 
              FROM projectdetail PD
              INNER JOIN users U ON PD.user_id = U.id
              ORDER BY PD.created_at ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'projects' => $projects
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>