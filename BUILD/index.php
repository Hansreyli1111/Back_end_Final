<?php
// include './config/connectdb.php';
// header('Content-Type: application/json');
// $method=$_SERVER['REQUEST_METHOD'];
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
// header('Access-Control-Allow-Headers: Content-Type');
// if($_SERVER['REQUEST_METHOD']==='OPTIONS'){
//     http_response_code(200);
//     exit();
// }
// $input=json_decode(file_get_contents('php://input'),true);
// $id= isset($_GET['id']) ? intval($_GET['id']):'';
// switch($method){
//     case "GET":
//         if($id){
//             $stmt=$connection->prepare('SELECT * FROM role WHERE role_id=?');
//             $stmt->bind_param('i',$id);
//             $stmt->execute();
//             $result=$stmt->get_result()->fetch_assoc();
//             //output the result as JSON
//             echo json_encode($result ?:["Message"=>"Role_ID Not Found"]);

//         }else{
//             //get all products
//             $result=$connection->query('SELECT * FROM role');
//             $rows=[];
//             while($row=$result->fetch_assoc()){
//                 $rows[]=$row;
//             }
//             echo json_encode($rows);
//         }
//     break;
//     case "POST":
//         $stmt=$connection
//         ->prepare("INSERT INTO role (role_name,created_at,status)
//         VALUES(?,?,?)");
//         $stmt->bind_param('sss',
//             $input['role_name'],
//             $input['created_at'],
//             $input['status']
//         );
//         $stmt->execute();
//         echo json_encode(["Message"=>"Role Added Successfully!"]);
//     break;
//     case "PUT":
//         if(!$id){
//             echo json_encode(["error"=>"Mssing Role_ID"]);
//             break;
//         }
//         // Check if product exists
//         $check = $connection->prepare("SELECT * FROM role WHERE role_id=?");
//         $check->bind_param("i", $id);
//         $check->execute();
//         $result = $check->get_result();

//         if ($result->num_rows == 0) {
//             echo json_encode([
//                 "success" => false,
//                 "message" => "Role ID $id not found!"
//             ]);
//             break;
//         }
        
//         $stmt=$connection->prepare("UPDATE role set role_name=?,created_at=?,status=? WHERE role_id=?");
//         $stmt->bind_param('sssi',
//             $input['role_name'],
//             $input['created_at'],
//             $input['status'],
//             $id
//         );
//         $stmt->execute();
//         echo json_encode(["Message"=>"Role Updated Successfully!"]);
//     break;
//     case "DELETE":
//         if(!$id){
//             echo json_encode(["error"=>"Mssing role_id"]);
//             break;
//         }
//         // Check if product exists
//         $check = $connection->prepare("SELECT role_id FROM role WHERE role_id = ?");
//         $check->bind_param("i", $id);
//         $check->execute();
//         $result = $check->get_result();

//         if ($result->num_rows == 0) {
//             echo json_encode([
//                 "success" => false,
//                 "message" => "Role ID $id not found!"
//             ]);
//             break;
//         }

//         $stmt=$connection->prepare("DELETE FROM role WHERE role_id=?");
//         $stmt->bind_param('i',$id);
//         $stmt->execute();
//         echo json_encode(["Message"=>"Product Deleted Successfully!"]);
//     break;
//     default:

//         http_response_code(405);
//         echo json_encode(["error"=>"Method Not Allowed"]);
//     break;
// }
include"./role.php";
?>
