<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include './config/connectdb.php';

try {
    // ត្រងរកផលិតផលណាដែលមានស្តុកតិចជាង ឬស្មើនឹង minimum_stock
    $query = "SELECT 
                product_code AS sku, 
                product_name AS item, 
                stock AS current_stock, 
                minimum_stock AS threshold 
              FROM product
              WHERE stock <= minimum_stock 
              ORDER BY stock ASC";
              
    $result = $connection->query($query);
    
    $lowStock = [];
    while ($row = $result->fetch_assoc()) {
        $lowStock[] = [
            "sku" => $row['sku'],
            "item" => $row['item'],
            "current" => (int)$row['current_stock'],
            "threshold" => (int)$row['threshold']
        ];
    }

    echo json_encode([
        "success" => true,
        "lowStockAlerts" => $lowStock
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "message" => $e->getMessage()
    ]);
}
?>