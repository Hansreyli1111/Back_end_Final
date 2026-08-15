<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include './config/connectdb.php';

try {
    // ធ្វើការ Map ឈ្មោះ Column ក្នុង Database ឱ្យត្រូវទៅនឹង Key ដែល React ត្រូវការ
    $query = "SELECT 
                product_code AS sku, 
                product_name AS name, 
                cost_price AS cost, 
                selling_price AS price, 
                stock AS qty 
              FROM product 
              ORDER BY product_name ASC";
              
    $result = $connection->query($query);
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            "sku" => $row['sku'],
            "name" => $row['name'],
            "cost" => (float)$row['cost'],
            "price" => (float)$row['price'],
            "qty" => (int)$row['qty']
        ];
    }

    echo json_encode([
        "success" => true,
        "items" => $items
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "message" => $e->getMessage()
    ]);
}
?>