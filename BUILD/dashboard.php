<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include './config/connectdb.php';

try {
    // ១. គណនាសរុបពីតារាង products
    $productQuery = "SELECT 
                        COUNT(*) as total_products,
                        SUM(stock) as total_quantity,
                        SUM(stock * selling_price) as total_valuation,
                        SUM(CASE WHEN stock <= 0 THEN 1 ELSE 0 END) as out_of_stock,
                        SUM(CASE WHEN stock > 0 AND stock <= minimum_stock THEN 1 ELSE 0 END) as low_stock,
                        SUM(CASE WHEN stock > minimum_stock THEN 1 ELSE 0 END) as in_stock
                     FROM product";
                     
    $productResult = $connection->query($productQuery);
    $stats = $productResult->fetch_assoc();

    // ២. ទាញយកទំនិញខើចស្តុក (Critical Items)
    $criticalQuery = "SELECT product_code as sku, product_name as name, 'Main Warehouse' as location, stock as onHand, minimum_stock as min, 
                      'CRITICAL' as status, 'text-red-600' as onHandColor, 'bg-red-100 text-red-600' as statusColor 
                      FROM product WHERE stock <= minimum_stock";
    $criticalResult = $connection->query($criticalQuery);
    $criticalItems = [];
    while ($row = $criticalResult->fetch_assoc()) {
        $criticalItems[] = $row;
    }

    // ៣. គណនា Supplier Performance ពី purchase_order ដូចមុន
    $supplierQuery = "SELECT COUNT(*) as total_orders, SUM(CASE WHEN total_amount > 0 THEN 1 ELSE 0 END) as successful_orders FROM purchase_order";
    $supplierResult = $connection->query($supplierQuery);
    $supplierData = $supplierResult->fetch_assoc();
    $totalOrders = intval($supplierData['total_orders']);
    $successfulOrders = intval($supplierData['successful_orders']);
    $performanceRate = $totalOrders > 0 ? round(($successfulOrders / $totalOrders) * 100, 1) : 100.0;

    // ៤. បញ្ចេញ JSON Response ឱ្យត្រូវរចនាសម្ព័ន្ធដែល React ត្រូវការ
    echo json_encode([
        "success" => true,
        "metrics" => [
            "totalProducts" => intval($stats['total_products'] ?? 0),
            "totalValuation" => floatval($stats['total_valuation'] ?? 0),
            "totalQuantity" => intval($stats['total_quantity'] ?? 0),
            "outOfStock" => intval($stats['out_of_stock'] ?? 0),
            "lowStock" => intval($stats['low_stock'] ?? 0),
            "inStock" => intval($stats['in_stock'] ?? 0),
            "supplierPerformance" => $performanceRate
        ],
        "criticalItems" => $criticalItems
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>