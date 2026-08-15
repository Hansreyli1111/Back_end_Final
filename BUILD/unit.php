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
        //get product by id if id is provided, else get all products
        if($id){
            $stmt=$connection->prepare('SELECT * FROM unit WHERE unit_id=?;
');
            $stmt->bind_param('i',$id);
            $stmt->execute();
            $result=$stmt->get_result()->fetch_assoc();
            //output the result as JSON
            echo json_encode($result ?:["Message"=>"UNIT_ID Not Found"]);

        }else{
            //get all products
            $result=$connection->query('SELECT * FROM unit ');
            $rows=[];
            while($row=$result->fetch_assoc()){
                $rows[]=$row;
            }
            echo json_encode($rows);
        }
    break;
    case "POST":
        $stmt=$connection
        ->prepare("INSERT INTO UNIT (unit_name,abbreviation)VALUE(?,?)");
        $stmt->bind_param('ss',
            $input['unit_name'],
            $input['abbreviation'],
        );
        $stmt->execute();
        echo json_encode(["Message"=>"Unit Added Successfully!"]);
    break;
    case "PUT":
        if(!$id){
            echo json_encode(["error"=>"Missing Unit Id"]);
            break;
        }
        // Check if product exists
        $check = $connection->prepare("SELECT * FROM unit WHERE unit_id=?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows == 0) {
            echo json_encode([
                "success" => false,
                "message" => "Unit ID $id not found!"
            ]);
            break;
        }
        
        $stmt=$connection->prepare("UPDATE unit set unit_name=?,abbreviation=? WHERE unit_id=?");
        $stmt->bind_param('ssi',
            $input['unit_name'],
            $input['abbreviation'],
            $id
        );
        $stmt->execute();
        echo json_encode(["Message"=>"Unit Updated Successfully!"]);
    break;
    case "DELETE":
        if(!$id){
            echo json_encode(["error"=>"Missing unit_id"]);
            break;
        }
        // Check if product exists
        $check = $connection->prepare("SELECT unit_id FROM unit WHERE unit_id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows == 0) {
            echo json_encode([
                "success" => false,
                "message" => "Unit ID $id not found!"
            ]);
            break;
        }

        $stmt=$connection->prepare("DELETE FROM unit WHERE unit_id=?");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        echo json_encode(["Message"=>"Unit   Deleted Successfully!"]);
    break;
    default:

        http_response_code(405);
        echo json_encode(["error"=>"Method Not Allowed"]);
    break;
}
?>