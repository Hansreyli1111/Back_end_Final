<?php
header("Access-Control-Allow-Origin: *");
include './config/connectdb.php';

// កំណត់ Export ជាទម្រង់ Excel (.xls)
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=activity_logs.xls");

try {
    // ប្រើ Query ដូចគ្នាទៅនឹង API របស់អ្នក
    $query = "SELECT 
                al.created_at as time, 
                u.username as user, 
                al.action, 
                al.module as entity, 
                al.status 
              FROM activity_log al
              LEFT JOIN user u ON al.user_id = u.user_id
              ORDER BY al.created_at DESC";

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
            background-color: #1E3A8A; 
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
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>TIMESTAMP</th>
                <th>USER</th>
                <th>ACTION</th>
                <th>ENTITY</th>
                <th>STATUS</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['time']) . "</td>";
                echo "<td>" . htmlspecialchars($row['user']) . "</td>";
                echo "<td>" . htmlspecialchars($row['action']) . "</td>";
                echo "<td>" . htmlspecialchars($row['entity']) . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5' class='text-center'>No activity logs found.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</body>
</html>