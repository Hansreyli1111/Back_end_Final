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
$input=json_decode(file_get_contents('php://input'),true);
$id= isset($_GET['id']) ? intval($_GET['id']):'';
switch($method){
    case "GET":
    //get role by id if id is provided, else get all roles
    if($id){
        // បើអ្នកចង់ឱ្យពេល get តាម id ក៏បង្ហាញ user_count ដែរ អាចប្រើ query នេះ
        $stmt = $connection->prepare('
            SELECT r.*, COUNT(u.user_id) AS user_count 
            FROM role r 
            LEFT JOIN user u ON r.role_id = u.role_id 
            WHERE r.role_id = ? 
            GROUP BY r.role_id
        ');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        //output the result as JSON
        echo json_encode($result ?: ["Message" => "Role_ID Not Found"]);

    }else{
        // ផ្នែកទាញយក Roles ទាំងអស់រួមទាំងចំនួន User
        $query = '
            SELECT r.*, COUNT(u.user_id) AS user_count 
            FROM role r 
            LEFT JOIN user u ON r.role_id = u.role_id 
            GROUP BY r.role_id
        ';
        $result = $connection->query($query);
        $rows = [];
        while($row = $result->fetch_assoc()){
            $rows[] = $row;
        }
        echo json_encode($rows);
    }
    break;
    case "POST":
        $role_name = $input['role_name'] ?? $input['name'] ?? '';
        $status = $input['status'] ?? 'Active';
        $created_at = $input['created_at'] ?? date('Y-m-d H:i:s');

        if (empty($role_name)) {
            http_response_code(400);
            echo json_encode(["error" => "Role name is required"]);
            break;
        }

        $stmt = $connection->prepare("INSERT INTO role (role_name, created_at, status) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $role_name, $created_at, $status);
        
        if ($stmt->execute()) {
            echo json_encode(["Message" => "Role Added Successfully!"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => $stmt->error]);
        }
        break;
    case "PUT":
        if(!$id){
            echo json_encode(["error"=>"Mssing Role_ID"]);
            break;
        }
        // Check if role exists
        $check = $connection->prepare("SELECT * FROM role WHERE role_id=?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows == 0) {
            echo json_encode([
                "success" => false,
                "message" => "Role ID $id not found!"
            ]);
            break;
        }
        
        $stmt=$connection->prepare("UPDATE role set role_name=?,created_at=?,status=? WHERE role_id=?");
        $stmt->bind_param('sssi',
            $input['role_name'],
            $input['created_at'],
            $input['status'],
            $id
        );
        $stmt->execute();
        echo json_encode(["Message"=>"Role Updated Successfully!"]);
    break;
    case "DELETE":
        if(!$id){
            echo json_encode(["error"=>"Missing role_id"]);
            break;
        }

        // ឆែកមើលថាតើមាន User កំពុងប្រើប្រាស់ Role នេះដែរឬទេ
        $checkUser = $connection->prepare("SELECT COUNT(*) as total FROM user WHERE role_id = ?");
        $checkUser->bind_param("i", $id);
        $checkUser->execute();
        $userCount = $checkUser->get_result()->fetch_assoc()['total'];

        if ($userCount > 0) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "Cannot delete this role because it is currently assigned to users."
            ]);
            break;
        }

        // បើគ្មាន User ប្រើប្រាស់ទេ ទើបអនុវត្តការលុប
        $stmt = $connection->prepare("DELETE FROM role WHERE role_id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        echo json_encode(["Message"=>"Role Deleted Successfully!"]);
        error_log("Trying to delete Role ID: " . $id . " with User Count: " . $userCount);
    break;
    default:

        http_response_code(405);
        echo json_encode(["error"=>"Method Not Allowed"]);
    break;
}
?>