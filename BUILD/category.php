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
            $stmt=$connection->prepare('SELECT * FROM category WHERE category_id=?');
            $stmt->bind_param('i',$id);
            $stmt->execute();
            $result=$stmt->get_result()->fetch_assoc();
            //output the result as JSON
            echo json_encode($result ?:["Message"=>"Category_ID Not Found"]);

        }else{
            //get all products
            $result=$connection->query('SELECT * FROM category');
            $rows=[];
            while($row=$result->fetch_assoc()){
                $rows[]=$row;
            }
            echo json_encode($rows);
        }
    break;
    case "POST":
        $stmt=$connection
        ->prepare("INSERT INTO CATEGORY (category_name,description)VALUE(?,?)");
        $stmt->bind_param('ss',
            $input['category_name'],
            $input['description'],
        );
        $stmt->execute();
        echo json_encode(["Message"=>"Category Added Successfully!"]);
    break;
    case "PUT":
        if(!$id){
            echo json_encode(["error"=>"Missing Category_Id"]);
            break;
        }
        // Check if product exists
        $check = $connection->prepare("SELECT * FROM category WHERE category_id=?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows == 0) {
            echo json_encode([
                "success" => false,
                "message" => "Category ID $id not found!"
            ]);
            break;
        }
        
        $stmt=$connection->prepare("UPDATE category set category_name=?,description=? WHERE category_id=?;");
        $stmt->bind_param('ssi',
            $input['category_name'],
            $input['description'],
            $id
        );
        $stmt->execute();
        echo json_encode(["Message"=>"Category Updated Successfully!"]);
    break;
    case "DELETE":
        if(!$id){
            echo json_encode(["error"=>"Missing category_id"]);
            break;
        }
        // Check if product exists
        $check = $connection->prepare("SELECT category_id FROM category WHERE category_id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows == 0) {
            echo json_encode([
                "success" => false,
                "message" => "Category ID $id not found!"
            ]);
            break;
        }

        $stmt=$connection->prepare("DELETE FROM category WHERE category_id=?");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        echo json_encode(["Message"=>"Category Deleted Successfully!"]);
    break;
    default:

        http_response_code(405);
        echo json_encode(["error"=>"Method Not Allowed"]);
    break;
    
}
?>