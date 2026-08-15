<?php
// Prevent any PHP warnings/errors from outputting raw HTML before JSON headers
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Allow cross-origin requests from React (Vite dev server)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Accept");

// Handle preflight OPTIONS request immediately
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Update database connection details according to your project setup
$host = 'localhost';
$dbname = 'inventory_management_system';
$username = 'li';
$password = '123';

try {
    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT * FROM purchase_order_detail ORDER BY purchase_order_detail_id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database Query Failed: " . $e->getMessage()]);
}
?>