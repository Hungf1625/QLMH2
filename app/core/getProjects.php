<?php
// projects_api.php
header('Content-Type: application/json');
session_start();
require_once '../core/databasePDO.php';

try {
    $query = "SELECT * FROM projectdetail WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
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