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
            // Fetch single stock in record
            $stmt = $pdo->prepare("SELECT * FROM stock_in WHERE stock_in_id = ?");
            $stmt->execute([$id]);
            $stockIn = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($stockIn) {
                $stmtDetails = $pdo->prepare("SELECT * FROM stock_in_detail WHERE stock_in_id = ?");
                $stmtDetails->execute([$id]);
                $stockIn['items'] = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($stockIn);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Stock in record not found"]);
            }
        } else {
            // Fetch all stock in records with their corresponding items
            $stmt = $pdo->query("SELECT * FROM stock_in ORDER BY stock_in_id DESC");
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($records as &$record) {
                $stmtDetails = $pdo->prepare("SELECT * FROM stock_in_detail WHERE stock_in_id = ?");
                $stmtDetails->execute([$record['stock_in_id']]);
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

            $purchase_order_id = !empty($data['purchase_order_id']) ? intval($data['purchase_order_id']) : null;
            $employee_id = $data['employee_id'];
            $branch_id = $data['branch_id'];
            $reference_no = $data['reference_no'] ?? null;
            $stock_date = $data['stock_date'] ?? date('Y-m-d');
            
            // Handle note field properly (converts empty string/null to database NULL)
            $note = !empty($data['note']) ? trim($data['note']) : null;

            // Insert into stock_in table
            $sql = "INSERT INTO stock_in (purchase_order_id, employee_id, branch_id, reference_no, stock_date, note) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$purchase_order_id, $employee_id, $branch_id, $reference_no, $stock_date, $note]);
            $stock_in_id = $pdo->lastInsertId();

            // Insert into stock_in_detail table
            if (isset($data['items']) && is_array($data['items'])) {
                $stmtItem = $pdo->prepare("INSERT INTO stock_in_detail (stock_in_id, product_id, quantity, unit_cost, expiry_date) VALUES (?, ?, ?, ?, ?)");
                foreach ($data['items'] as $item) {
                    $product_id = $item['product_id'];
                    $quantity = $item['quantity'];
                    $unit_cost = $item['unit_cost'] ?? 0.00;
                    $expiry_date = !empty($item['expiry_date']) ? $item['expiry_date'] : null;

                    $stmtItem->execute([$stock_in_id, $product_id, $quantity, $unit_cost, $expiry_date]);
                }
            }

            $pdo->commit();
            echo json_encode(["success" => true, "stock_in_id" => $stock_in_id]);
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

            $purchase_order_id = !empty($data['purchase_order_id']) ? intval($data['purchase_order_id']) : null;
            $employee_id = $data['employee_id'] ?? 1;
            $branch_id = $data['branch_id'] ?? 1;
            $reference_no = $data['reference_no'] ?? null;
            $stock_date = $data['stock_date'] ?? date('Y-m-d');
            
            // Handle note update properly
            $note = !empty($data['note']) ? trim($data['note']) : null;

            // Update stock_in table
            $sql = "UPDATE stock_in SET purchase_order_id = ?, employee_id = ?, branch_id = ?, reference_no = ?, stock_date = ?, note = ? WHERE stock_in_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$purchase_order_id, $employee_id, $branch_id, $reference_no, $stock_date, $note, $id]);

            // Refresh details: clear old rows and insert updated list
            if (isset($data['items']) && is_array($data['items'])) {
                $stmtDel = $pdo->prepare("DELETE FROM stock_in_detail WHERE stock_in_id = ?");
                $stmtDel->execute([$id]);

                $stmtItem = $pdo->prepare("INSERT INTO stock_in_detail (stock_in_id, product_id, quantity, unit_cost, expiry_date) VALUES (?, ?, ?, ?, ?)");
                foreach ($data['items'] as $item) {
                    $product_id = $item['product_id'];
                    $quantity = $item['quantity'];
                    $unit_cost = $item['unit_cost'] ?? 0.00;
                    $expiry_date = !empty($item['expiry_date']) ? $item['expiry_date'] : null;

                    $stmtItem->execute([$id, $product_id, $quantity, $unit_cost, $expiry_date]);
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
            $stmtDetails = $pdo->prepare("DELETE FROM stock_in_detail WHERE stock_in_id = ?");
            $stmtDetails->execute([$id]);

            // Delete master record
            $stmt = $pdo->prepare("DELETE FROM stock_in WHERE stock_in_id = ?");
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