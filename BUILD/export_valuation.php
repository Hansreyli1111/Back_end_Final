<?php
header("Access-Control-Allow-Origin: *");
include './config/connectdb.php';

// កំណត់ Header សម្រាប់ CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=valuation_report.csv');

$output = fopen('php://output', 'w');

// បន្ថែម UTF-8 BOM ដើម្បីឱ្យ Excel បើកបង្ហាញ Font បានត្រឹមត្រូវ
fputs($output, "\xEF\xBB\xBF");

// បង្កើត Header Row
fputcsv($output, array('SKU', 'Product Name', 'Unit Cost', 'Unit Price', 'Quantity', 'Total Cost Value'));

// ទាញយកទិន្នន័យពី Database
$query = "SELECT 
            product_code AS sku, 
            product_name AS name, 
            cost_price AS cost, 
            selling_price AS price, 
            stock AS qty 
          FROM product
          ORDER BY product_name ASC";

$result = $connection->query($query);

while ($row = $result->fetch_assoc()) {
    $cost = (float)$row['cost'];
    $price = (float)$row['price'];
    $qty = (int)$row['qty'];
    $total_cost = $cost * $qty;

    // រៀបចំទម្រង់ទិន្នន័យឱ្យស្អាត ($19.99, $21.00, $1,999.00)
    fputcsv($output, array(
        $row['sku'],
        $row['name'],
        '$' . number_format($cost, 2),
        '$' . number_format($price, 2),
        number_format($qty),
        '$' . number_format($total_cost, 2)
    ));
}

fclose($output);
exit();
?>