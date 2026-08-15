<?php
header("Access-Control-Allow-Origin: *");
include './config/connectdb.php';

// កំណត់ Export ជាទម្រង់ Excel (.xls)
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=low_stock_alerts.xls");

try {
    // កែសម្រួល Column ឱ្យត្រូវទៅនឹងตារាង product របស់អ្នក
    $query = "SELECT 
                product_name, 
                product_code AS sku, 
                'General' AS category, 
                stock AS stock_quantity, 
                minimum_stock AS min_stock_level, 
                'Low Stock' AS status 
              FROM product
              WHERE stock <= minimum_stock 
              ORDER BY stock ASC";

    $result = $connection->query($query);
} catch (Exception $e) {
    $result = false;
}
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
        th { 
            background-color: #B91C1C; 
            color: #FFFFFF; 
            font-weight: bold; 
            text-align: left; 
            padding: 8px; 
            border: 1px solid #991B1B; 
        }
        td { 
            padding: 6px 8px; 
            border: 1px solid #D1D5DB; 
            font-size: 13px;
        }
        .text-center { text-align: center; }
        .text-danger { color: #DC2626; font-weight: bold; }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>SKU</th>
                <th>Category</th>
                <th>Current Stock</th>
                <th>Min Level</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['sku']) . "</td>";
                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                echo "<td class='text-danger'>" . htmlspecialchars($row['stock_quantity']) . "</td>";
                echo "<td>" . htmlspecialchars($row['min_stock_level']) . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6' class='text-center'>No low stock alerts found.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</body>
</html>