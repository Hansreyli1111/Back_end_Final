<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration — adjust credentials to match your setup
$host = 'localhost';
$dbname = 'inventory_management_system';
$username = 'li';
$password = '123'; // Put your DB password here if you have one

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    http_response_code(500);
    echo json_encode(["error" => "Connection error: " . $exception->getMessage()]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch($method) {
    case 'GET':
        if ($id) {
            // Fetch single stock out record
            $stmt = $pdo->prepare("SELECT * FROM stock_out WHERE stock_out_id = ?");
            $stmt->execute([$id]);
            $stockOut = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($stockOut) {
                $stmtDetails = $pdo->prepare("SELECT * FROM stock_out_detail WHERE stock_out_id = ?");
                $stmtDetails->execute([$id]);
                $stockOut['items'] = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($stockOut);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Stock out record not found"]);
            }
        } else {
            // Fetch all stock out records with their corresponding items
            $stmt = $pdo->query("SELECT * FROM stock_out ORDER BY stock_out_id DESC");
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($records as &$record) {
                $stmtDetails = $pdo->prepare("SELECT * FROM stock_out_detail WHERE stock_out_id = ?");
                $stmtDetails->execute([$record['stock_out_id']]);
                $record['items'] = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);
            }
            echo json_encode($records);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['employee_id']) || !isset($data['branch_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields (employee_id, branch_id)"]);
            exit();
        }

        try {
            $pdo->beginTransaction();

            $employee_id = $data['employee_id'];
            $branch_id = $data['branch_id'];
            $reference_no = $data['reference_no'] ?? null;
            $stock_date = $data['stock_date'] ?? date('Y-m-d');
            
            // Crucial: Handle purpose field properly (converts empty string/null to database NULL)
            $purpose = !empty($data['purpose']) ? trim($data['purpose']) : null;

            // Insert into stock_out table
            $sql = "INSERT INTO stock_out (employee_id, branch_id, reference_no, stock_date, purpose) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$employee_id, $branch_id, $reference_no, $stock_date, $purpose]);
            $stock_out_id = $pdo->lastInsertId();

            // Insert into stock_out_detail table
            if (isset($data['items']) && is_array($data['items'])) {
                $stmtItem = $pdo->prepare("INSERT INTO stock_out_detail (stock_out_id, product_id, quantity, selling_price) VALUES (?, ?, ?, ?)");
                foreach ($data['items'] as $item) {
                    $product_id = $item['product_id'];
                    $quantity = $item['quantity'];
                    $selling_price = $item['selling_price'] ?? 0.00;
                    $stmtItem->execute([$stock_out_id, $product_id, $quantity, $selling_price]);
                }
            }

            $pdo->commit();
            echo json_encode(["success" => true, "stock_out_id" => $stock_out_id]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Missing ID for update"]);
            exit();
        }

        $data = json_decode(file_get_contents("php://input"), true);

        try {
            $pdo->beginTransaction();

            $employee_id = $data['employee_id'] ?? 1;
            $branch_id = $data['branch_id'] ?? 1;
            $reference_no = $data['reference_no'] ?? null;
            $stock_date = $data['stock_date'] ?? date('Y-m-d');
            
            // Crucial: Handle purpose update properly
            $purpose = !empty($data['purpose']) ? trim($data['purpose']) : null;

            // Update stock_out table
            $sql = "UPDATE stock_out SET employee_id = ?, branch_id = ?, reference_no = ?, stock_date = ?, purpose = ? WHERE stock_out_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$employee_id, $branch_id, $reference_no, $stock_date, $purpose, $id]);

            // Refresh details: clear old rows and insert updated list
            if (isset($data['items']) && is_array($data['items'])) {
                $stmtDel = $pdo->prepare("DELETE FROM stock_out_detail WHERE stock_out_id = ?");
                $stmtDel->execute([$id]);

                $stmtItem = $pdo->prepare("INSERT INTO stock_out_detail (stock_out_id, product_id, quantity, selling_price) VALUES (?, ?, ?, ?)");
                foreach ($data['items'] as $item) {
                    $product_id = $item['product_id'];
                    $quantity = $item['quantity'];
                    $selling_price = $item['selling_price'] ?? 0.00;
                    $stmtItem->execute([$id, $product_id, $quantity, $selling_price]);
                }
            }

            $pdo->commit();
            echo json_encode(["success" => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Missing ID for deletion"]);
            exit();
        }

        try {
            $pdo->beginTransaction();

            // Delete child details first to respect foreign key constraints
            $stmtDetails = $pdo->prepare("DELETE FROM stock_out_detail WHERE stock_out_id = ?");
            $stmtDetails->execute([$id]);

            // Delete master record
            $stmt = $pdo->prepare("DELETE FROM stock_out WHERE stock_out_id = ?");
            $stmt->execute([$id]);

            $pdo->commit();
            echo json_encode(["success" => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
?>