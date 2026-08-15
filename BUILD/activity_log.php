<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include './config/connectdb.php';

try {
    // ធ្វើការ JOIN ជាមួយតារាង users ដើម្បីទាញយកឈ្មោះ user (username ឬ full_name) មកបង្ហាញ
    $query = "SELECT 
                al.created_at as time, 
                u.username as user, 
                al.action, 
                al.module as entity, 
                al.status 
              FROM activity_log al
              LEFT JOIN user u ON al.user_id = u.user_id
              ORDER BY al.created_at DESC";

    $result = $connection->query($query);
    
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }

    echo json_encode([
        "success" => true,
        "activityLogs" => $logs
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "message" => $e->getMessage()
    ]);
}
?>