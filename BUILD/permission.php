<?php
include './config/connectdb.php';
header('Content-Type: application/json');
$method=$_SERVER['REQUEST_METHOD'];
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if($_SERVER['REQUEST_METHOD']==='OPTIONS'){
    http_response_code(200);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($_GET['id']) ? intval($_GET['id']) : '';

switch ($method) {
    case "GET":
        if ($id) {
            // ទាញយក Permission តាម ID
            $stmt = $connection->prepare("SELECT * FROM permission WHERE permission_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            echo json_encode($result ?: ["message" => "Permission ID Not Found"]);
        } else {
            // ទាញយក Permissions ទាំងអស់ (កែសម្រួលទ្រង់ទ្រាយ JSON ឱ្យត្រូវជាមួយ React)
            $result = $connection->query("SELECT * FROM permission");
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            echo json_encode([
                "success" => true,
                "permissions" => $rows
            ]);
        }
        break;

    case "POST":
        // បង្កើត Permission ថ្មី
$data = json_decode(file_get_contents("php://input"), true);
$permission_name = $data['permission_name'] ?? '';
$module = $data['module'] ?? '';
$action = $data['action'] ?? '';
$status = $data['status'] ?? 'Active';
        
        if (!empty($permission_name) && !empty($module) && !empty($action)) {
            $stmt = $connection->prepare("INSERT INTO permission (permission_name, module, action, status, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param("ssss", $permission_name, $module, $action, $status);
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Permission created successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed to create permission"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Permission name, module, and action are required"]);
        }
        break;

    case "PUT":
        // កែប្រែ (Update) Permission
        $data = json_decode(file_get_contents("php://input"), true);
        $permission_id = $data['permission_id'] ?? $id;
        $permission_name = $data['permission_name'] ?? '';
        $module = $data['module'] ?? '';
        $action = $data['action'] ?? '';
        $status = $data['status'] ?? 'Active';

        if ($permission_id && !empty($permission_name)) {
            $stmt = $connection->prepare("UPDATE permission SET permission_name = ?, module = ?, action = ?, status = ?, updated_at = NOW() WHERE permission_id = ?");
            $stmt->bind_param("ssssi", $permission_name, $module, $action, $status, $permission_id);
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Permission updated successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed to update permission"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "ID and Permission name are required"]);
        }
        break;

    case "DELETE":
        // លុប (Delete) Permission
        $permission_id = $id;
        
        if ($permission_id) {
            $stmt = $connection->prepare("DELETE FROM permission WHERE permission_id = ?");
            $stmt->bind_param("i", $permission_id);
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Permission deleted successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed to delete permission"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Permission ID is required"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not supported"]);
        break;
}
?>