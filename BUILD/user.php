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

switch($method){
    case "GET":
        if($id){
            $stmt = $connection->prepare('SELECT * FROM user WHERE user_id=?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            echo json_encode($result ?: ["Message" => "User Not Found"]);
        } else {
            $result = $connection->query('SELECT * FROM user');
            $rows = [];
            while($row = $result->fetch_assoc()){
                $rows[] = $row;
            }
            echo json_encode($rows);
        }
    break;

    case "POST":
        $username   = $input['username'] ?? '';
        $email      = $input['email'] ?? '';
        $rawPassword= $input['password'] ?? '';
        $hashedPassword = !empty($rawPassword) ? password_hash($rawPassword, PASSWORD_BCRYPT) : '';
        
        $roleId     = intval($input['role_id'] ?? $input['role'] ?? 1); 
        $employeeId = !empty($input['employee_id']) ? intval($input['employee_id']) : null;
        $lastLogin  = $input['last_login'] ?? null;

        $stmt = $connection->prepare("INSERT INTO user (employee_id, role_id, username, password, email, last_login) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iissss',
            $employeeId,
            $roleId,
            $username,
            $hashedPassword,
            $email,
            $lastLogin
        );
        
        if($stmt->execute()){
            echo json_encode(["Message" => "User Added Successfully!"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => $stmt->error]);
        }
    break;

    case "PUT":
        $userId   = intval($input['user_id'] ?? 0);
        $username = $input['username'] ?? '';
        $email    = $input['email'] ?? '';
        $roleId   = intval($input['role_id'] ?? 1);
        $rawPassword = $input['password'] ?? '';

        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid or Missing User ID for update"]);
            break;
        }

        // ឆែកមើលថាតើមានការប្តូរ Password ថ្មីដែរឬទេ
        if (!empty($rawPassword)) {
            $hashedPassword = password_hash($rawPassword, PASSWORD_BCRYPT);
            $stmt = $connection->prepare("UPDATE user SET username = ?, email = ?, role_id = ?, password = ? WHERE user_id = ?");
            $stmt->bind_param("ssisi", $username, $email, $roleId, $hashedPassword, $userId);
        } else {
            // អត់មានប្តូរ Password គឺ Update តែ username, email, និង role_id
            $stmt = $connection->prepare("UPDATE user SET username = ?, email = ?, role_id = ? WHERE user_id = ?");
            $stmt->bind_param("ssii", $username, $email, $roleId, $userId);
        }

        if ($stmt->execute()) {
            echo json_encode(["message" => "User updated successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => $stmt->error]);
        }
    break;
        case "DELETE":
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid or Missing ID for deletion"]);
            break;
        }

        $stmt = $connection->prepare("DELETE FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "User deleted successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => $stmt->error]);
        }
    break;
    default:
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
    break;
}
?>