<?php
include './config/connectdb.php';
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($_GET['id']) ? intval($_GET['id']) : '';

switch ($method) {
    case "GET":
        if ($id) {
            $stmt = $connection->prepare('SELECT * FROM product WHERE product_id=?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            echo json_encode($result ?: ["Message" => "Product_ID Not Found"]);
        } else {
            $result = $connection->query('SELECT * FROM product');
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            echo json_encode($rows);
        }
        break;

    case "POST":
        // បន្ថែម stock ចូលក្នុង INSERT query
        $stmt = $connection->prepare("INSERT INTO product(category_id, brand_id, unit_id, product_code, product_name, description, cost_price, selling_price, stock, minimum_stock, barcode, status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");
        
        // Types: i = int, s = string, d = double/float (សរុបមានប្រភេទ data ត្រូវគ្នា)
        $stmt->bind_param('iiisssddiiss',
            $input['category_id'],
            $input['brand_id'],
            $input['unit_id'],
            $input['product_code'],
            $input['product_name'],
            $input['description'],
            $input['cost_price'],
            $input['selling_price'],
            $input['stock'],         // បន្ថែម stock នៅទីនេះ
            $input['minimum_stock'],
            $input['barcode'],
            $input['status']
        );
        $stmt->execute();
        echo json_encode(["Message" => "Product Added Successfully!"]);
        break;

    case "PUT":
        if (!$id) {
            echo json_encode(["error" => "Missing Product_ID"]);
            break;
        }

        $check = $connection->prepare("SELECT * FROM product WHERE product_id=?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows == 0) {
            echo json_encode([
                "success" => false,
                "message" => "Product ID $id not found!"
            ]);
            break;
        }
        
        // បន្ថែម stock=? ចូលក្នុង UPDATE query
        $stmt = $connection->prepare("UPDATE product SET category_id=?, brand_id=?, unit_id=?, product_name=?, product_code=?, description=?, cost_price=?, selling_price=?, stock=?, minimum_stock=?, barcode=?, status=? WHERE product_id=?");
        
        $stmt->bind_param('iiisssddiissi',
            $input['category_id'],
            $input['brand_id'],
            $input['unit_id'],
            $input['product_name'],
            $input['product_code'],
            $input['description'],
            $input['cost_price'],
            $input['selling_price'],
            $input['stock'],         // បន្ថែម stock នៅទីនេះ
            $input['minimum_stock'],
            $input['barcode'],
            $input['status'],
            $id
        );
        $stmt->execute();
        echo json_encode(["Message" => "Product Updated Successfully!"]);
        break;

    case "DELETE":
        if (!$id) {
            echo json_encode(["error" => "Missing product_id"]);
            break;
        }

        $check = $connection->prepare("SELECT product_id FROM product WHERE product_id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows == 0) {
            echo json_encode([
                "success" => false,
                "message" => "Product ID $id not found!"
            ]);
            break;
        }

        $stmt = $connection->prepare("DELETE FROM product WHERE product_id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        echo json_encode(["Message" => "Product Deleted Successfully!"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
        break;
}
?>