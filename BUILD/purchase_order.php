<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Accept");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Points 1 level up from BUILD/ to Config/connectdb.php
require_once '../Config/connectdb.php';

/** @var PDO $connection */

$method = $_SERVER['REQUEST_METHOD'];

/* --- GET: Fetch Purchase Orders with Details --- */
if ($method === 'GET') {
    try {
        $sql = "SELECT po.*, s.supplier_name, 
                       COALESCE(SUM(pod.quantity), 0) AS total_items
                FROM purchase_order po
                JOIN supplier s ON po.supplier_id = s.supplier_id
                LEFT JOIN purchase_order_detail pod ON po.purchase_order_id = pod.purchase_order_id AND pod.status = 'Active'
                WHERE po.status = 'Active'
                GROUP BY po.purchase_order_id
                ORDER BY po.purchase_order_id DESC";

        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($data);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Fetch failed: " . $e->getMessage()]);
    }
    exit();
}

/* --- POST: Create Purchase Order with Line Items --- */
if ($method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input || empty($input['supplier_id']) || empty($input['branch_id']) || empty($input['employee_id']) || empty($input['po_number'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields (supplier_id, branch_id, employee_id, po_number)"]);
        exit();
    }

    try {
        $connection->beginTransaction();

        $headerSql = "INSERT INTO purchase_order (supplier_id, branch_id, employee_id, po_number, order_date, expected_date, total_amount) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($headerSql);
        
        $totalAmount = $input['total_amount'] ?? 0.00;

        $stmt->execute([
            $input['supplier_id'],
            $input['branch_id'],
            $input['employee_id'],
            $input['po_number'],
            $input['order_date'] ?? date('Y-m-d'),
            $input['expected_date'] ?? null,
            $totalAmount
        ]);

        $poId = $connection->lastInsertId();

        if (!empty($input['items']) && is_array($input['items'])) {
            $itemSql = "INSERT INTO purchase_order_detail (purchase_order_id, product_id, quantity, unit_cost, subtotal) 
                        VALUES (?, ?, ?, ?, ?)";
            $itemStmt = $connection->prepare($itemSql);

            foreach ($input['items'] as $item) {
                if (!empty($item['product_id'])) {
                    $qty = $item['quantity'] ?? 1;
                    $cost = $item['unit_cost'] ?? 0.00;
                    $subtotal = $qty * $cost;

                    $itemStmt->execute([
                        $poId,
                        $item['product_id'],
                        $qty,
                        $cost,
                        $subtotal
                    ]);
                }
            }
        }

        $connection->commit();
        http_response_code(201);
        echo json_encode(["success" => true, "purchase_order_id" => $poId]);
    } catch (PDOException $e) {
        $connection->rollBack();
        http_response_code(500);
        echo json_encode(["message" => "Save failed: " . $e->getMessage()]);
    }
    exit();
}

/* --- DELETE: Soft Delete Purchase Order --- */
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(["message" => "Missing ID"]);
        exit();
    }

    try {
        $connection->beginTransaction();

        $stmt1 = $connection->prepare("UPDATE purchase_order_detail SET status = 'Inactive' WHERE purchase_order_id = ?");
        $stmt1->execute([$id]);

        $stmt2 = $connection->prepare("UPDATE purchase_order SET status = 'Inactive' WHERE purchase_order_id = ?");
        $stmt2->execute([$id]);

        $connection->commit();
        echo json_encode(["success" => true]);
    } catch (PDOException $e) {
        $connection->rollBack();
        http_response_code(500);
        echo json_encode(["message" => "Delete failed: " . $e->getMessage()]);
    }
    exit();
}