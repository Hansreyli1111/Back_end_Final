<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include './config/connectdb.php'; // ផ្លាស់ប្តូរទីតាំងឯកសារតភ្ជាប់ Database តាមជាក់ស្តែង

try {
    // ធ្វើការ Query ទាញយកទិន្នន័យផលិតផលពី Database
    // (សូមដូរឈ្មោះตារាង 'products' ទៅជាឈ្មោះពិតប្រាកដរបស់អ្នក ប្រសិនបើវាខុសគ្នា)
    $query = "SELECT 
                product_code as sku, 
                product_name as name, 
                'General' as category,          -- បើមានตារាង category អាច JOIN បន្ថែម
                'Main Warehouse' as warehouse,  -- បើមានตារាង warehouse អាច JOIN បន្ថែម
                stock, 
                minimum_stock as lowLimit 
              FROM product";

    $result = $connection->query($query);
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            "sku" => $row['sku'],
            "name" => $row['name'],
            "category" => $row['category'],
            "warehouse" => $row['warehouse'],
            "stock" => intval($row['stock']),
            "lowLimit" => intval($row['lowLimit'])
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