<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include './config/connectdb.php';

// ទទួល raw POST data
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput);

if(isset($data->supplier_id, $data->po_number, $data->total_amount)) {
    $supplier_id = intval($data->supplier_id);
    $branch_id = intval($data->branch_id ?? 1);
    $employee_id = intval($data->employee_id ?? 1);
    $po_number = $connection->real_escape_string($data->po_number);
    $order_date = $connection->real_escape_string($data->order_date ?? date('Y-m-d'));
    $expected_date = $connection->real_escape_string($data->expected_date ?? date('Y-m-d'));
    $total_amount = floatval($data->total_amount);

    // ១. កំណត់អថេរ $query សម្រាប់បញ្ចូលទិន្នន័យចូលតារាង purchase_order
    $query = "INSERT INTO purchase_order (supplier_id, branch_id, employee_id, po_number, order_date, expected_date, total_amount, created_at, updated_at) 
              VALUES ($supplier_id, $branch_id, $employee_id, '$po_number', '$order_date', '$expected_date', $total_amount, NOW(), NOW())";

    if($connection->query($query)) {
        // ២. បន្ទាប់ពីបញ្ចូលជោគជ័យ យើងអាចគណនា Supplier Performance បន្ថែមដើម្បីផ្ញើត្រឡប់ទៅវិញបើត្រូវការ
        $supplierQuery = "SELECT 
                            COUNT(*) as total_orders,
                            SUM(CASE WHEN total_amount > 0 THEN 1 ELSE 0 END) as successful_orders 
                          FROM purchase_order";
                          
        $supplierResult = $connection->query($supplierQuery);
        $supplierData = $supplierResult->fetch_assoc();

        $totalOrders = intval($supplierData['total_orders']);
        $successfulOrders = intval($supplierData['successful_orders']);
        $performanceRate = $totalOrders > 0 ? round(($successfulOrders / $totalOrders) * 100, 1) : 100.0;

        echo json_encode([
            "success" => true, 
            "message" => "Purchase Order created successfully!",
            "supplierPerformance" => $performanceRate
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Database Error: " . $connection->error]);
    }
} else {
    echo json_encode([
        "success" => false, 
        "message" => "Incomplete data provided!",
        "debug_input" => $rawInput
    ]);
}
?>