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
            $stmt=$connection->prepare('SELECT * FROM brand WHERE brand_id=?');
            $stmt->bind_param('i',$id);
            $stmt->execute();
            $result=$stmt->get_result()->fetch_assoc();
            //output the result as JSON
            echo json_encode($result ?:["Message"=>"Brand _ID Not Found"]);

        }else{
            //get all products
            $result=$connection->query('SELECT * FROM brand');
            $rows=[];
            while($row=$result->fetch_assoc()){
                $rows[]=$row;
            }
            echo json_encode($rows);
        }
    break;
   case "POST":
        $stmt = $connection->prepare("INSERT INTO brand (brand_name, website, origin) VALUES (?, ?, ?)");
        $stmt->bind_param('sss',
            $input['brand_name'],
            $input['website'],
            $input['origin']
        );
        $stmt->execute();
        echo json_encode(["Message" => "Brand Added Successfully!"]);
    break;

    case "PUT":
        if(!$id){
            echo json_encode(["error" => "Missing Brand _Id"]);
            break;
        }
        // Check if brand exists
        $check = $connection->prepare("SELECT * FROM brand WHERE brand_id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows == 0) {
            echo json_encode([
              "success" => false,
              "message" => "Brand ID $id not found!"
            ]);
            break;
        }
        
        $stmt = $connection->prepare("UPDATE brand SET brand_name = ?, website = ?, origin = ? WHERE brand_id = ?");
        $stmt->bind_param('sssi',
            $input['brand_name'],
            $input['website'],
            $input['origin'],
            $id
        );
        $stmt->execute();
        echo json_encode(["Message" => "Brand Updated Successfully!"]);
    break;
    case "DELETE":
        if(!$id){
            echo json_encode(["error"=>"Missing brand_id"]);
            break;
        }
        // Check if product exists
        $check = $connection->prepare("SELECT brand_id FROM brand WHERE brand_id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows == 0) {
            echo json_encode([
                "success" => false,
                "message" => "Brand ID $id not found!"
            ]);
            break;
        }

        $stmt=$connection->prepare("DELETE FROM brand WHERE brand_id=?");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        echo json_encode(["Message"=>"Brand Deleted Successfully!"]);
    break;
    default:

        http_response_code(405);
        echo json_encode(["error"=>"Method Not Allowed"]);
    break;
}
?>