<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include './config/connectdb.php';

try {
    // ទាញយកទិន្នន័យពី stock_transfer ដោយ JOIN ជាមួយ products ដើម្បីយក product_name និង product_code (sku)
    $query = "SELECT 
                st.transfer_date as time, 
                p.product_name as product, 
                p.product_code as sku, 
                'STOCK TRANSFER' as type, 
                CONCAT('+', st.quantity) as qty, 
                'Admin' as actor 
              FROM stock_transfer st
              LEFT JOIN product p ON st.product_id = p.product_id
              ORDER BY st.created_at DESC";

    $result = $connection->query($query);
    
    $movements = [];
    while ($row = $result->fetch_assoc()) {
        $movements[] = $row;
    }

    echo json_encode([
        "success" => true,
        "movements" => $movements
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "message" => $e->getMessage()
    ]);
}
?>