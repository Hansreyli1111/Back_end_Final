<?php
header("Access-Control-Allow-Origin: *");
include './config/connectdb.php';

// កំណត់ Header ឱ្យទាញយកចេញជាឯកសារ Excel (.xls)
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=inventory_summary.xls");

try {
    $query = "SELECT 
                product_code as sku, 
                product_name as name, 
                'General' as category, 
                'Main Warehouse' as warehouse, 
                stock, 
                minimum_stock as lowLimit 
              FROM product";

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
            background-color: #0F766E; 
            color: #FFFFFF; 
            font-weight: bold; 
            text-align: left; 
            padding: 8px; 
            border: 1px solid #115E59; 
        }
        td { 
            padding: 6px 8px; 
            border: 1px solid #D1D5DB; 
            font-size: 13px;
        }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Warehouse</th>
                <th>Stock</th>
                <th>Low Limit</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['sku']) . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                echo "<td>" . htmlspecialchars($row['warehouse']) . "</td>";
                echo "<td class='text-center'>" . intval($row['stock']) . "</td>";
                echo "<td class='text-center'>" . intval($row['lowLimit']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6' class='text-center'>No inventory items found.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</body>
</html>