<?php
header("Access-Control-Allow-Origin: *");
include './config/connectdb.php';

// កំណត់ Export ជាទម្រង់ Excel (.xls)
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=movement_history.xls");

try {
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
            background-color: #1E40AF; 
            color: #FFFFFF; 
            font-weight: bold; 
            text-align: left; 
            padding: 8px; 
            border: 1px solid #1E3A8A; 
        }
        td { 
            padding: 6px 8px; 
            border: 1px solid #D1D5DB; 
            font-size: 13px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .sku-sub { font-size: 11px; color: #6B7280; }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Product Name / SKU</th>
                <th>Type</th>
                <th class="text-right">Quantity</th>
                <th>User</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['time']) . "</td>";
                echo "<td>" . htmlspecialchars($row['product']) . "<br><span class='sku-sub'>" . htmlspecialchars($row['sku']) . "</span></td>";
                echo "<td>" . htmlspecialchars($row['type']) . "</td>";
                echo "<td class='text-right'>" . htmlspecialchars($row['qty']) . "</td>";
                echo "<td>" . htmlspecialchars($row['actor']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5' class='text-center'>No movement history found.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</body>
</html>