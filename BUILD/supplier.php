<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Accept");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Points 1 level up from BUILD/ to Config/connectdb.php
require_once '../Config/connectdb.php';

if (file_exists('connectdb.php')) {
    require_once 'connectdb.php';
} else if (file_exists('../Config/connectdb.php')) {
    require_once '../Config/connectdb.php';
}

$db = $connection ?? $conn ?? $pdo ?? $db ?? null;

if (!$db) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed or variable not found in connectdb.php"]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true) ?? [];

/* --- GET REQUEST --- */
if ($method === 'GET') {
    $id = $_GET['supplier_id'] ?? $_GET['id'] ?? null;

    // PDO Connection
    if ($db instanceof PDO) {
        try {
            if ($id) {
                $stmt = $db->prepare("SELECT * FROM supplier WHERE supplier_id = ?");
                $stmt->execute([$id]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($data ?: (object)[]);
            } else {
                $stmt = $db->prepare("SELECT * FROM supplier ORDER BY supplier_id DESC");
                $stmt->execute();
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
        } catch (PDOException $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        exit();
    }

    // MYSQLI Connection
    if ($db instanceof mysqli) {
        if ($id) {
            $query = "SELECT * FROM supplier WHERE supplier_id = " . intval($id);
            $res = $db->query($query);
            echo json_encode($res ? $res->fetch_assoc() : (object)[]);
        } else {
            $res = $db->query("SELECT * FROM supplier ORDER BY supplier_id DESC");
            $data = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
            echo json_encode($data);
        }
        exit();
    }
}

/* --- POST REQUEST (CREATE) --- */
if ($method === 'POST') {
    $supplier_name  = $input['supplier_name'] ?? $input['name'] ?? null;
    $contact_person = $input['contact_person'] ?? null;
    $phone          = $input['phone'] ?? null;
    $email          = $input['email'] ?? null;
    $address        = $input['address'] ?? null;
    $payment_term   = $input['payment_term'] ?? null;
    $status         = $input['status'] ?? 'Active';

    if (empty($supplier_name)) {
        http_response_code(400);
        echo json_encode(["message" => "Supplier name required"]);
        exit();
    }

    if ($db instanceof PDO) {
        $stmt = $db->prepare("INSERT INTO supplier (supplier_name, contact_person, phone, email, address, payment_term, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([$supplier_name, $contact_person, $phone, $email, $address, $payment_term, $status]);
        echo json_encode(["success" => $success, "id" => $db->lastInsertId()]);
    } else if ($db instanceof mysqli) {
        $name    = $db->real_escape_string($supplier_name);
        $contact = $db->real_escape_string($contact_person ?? '');
        $ph      = $db->real_escape_string($phone ?? '');
        $em      = $db->real_escape_string($email ?? '');
        $addr    = $db->real_escape_string($address ?? '');
        $pay     = $db->real_escape_string($payment_term ?? '');
        $st      = $db->real_escape_string($status);

        $sql = "INSERT INTO supplier (supplier_name, contact_person, phone, email, address, payment_term, status) VALUES ('$name', '$contact', '$ph', '$em', '$addr', '$pay', '$st')";
        $success = $db->query($sql);
        echo json_encode(["success" => $success, "id" => $db->insert_id]);
    }
    exit();
}

/* --- PUT REQUEST (UPDATE) --- */
if ($method === 'PUT') {
    $id             = $input['supplier_id'] ?? $input['id'] ?? $_GET['supplier_id'] ?? $_GET['id'] ?? null;
    $supplier_name  = $input['supplier_name'] ?? $input['name'] ?? null;
    $contact_person = $input['contact_person'] ?? null;
    $phone          = $input['phone'] ?? null;
    $email          = $input['email'] ?? null;
    $address        = $input['address'] ?? null;
    $payment_term   = $input['payment_term'] ?? null;
    $status         = $input['status'] ?? 'Active';

    if (!$id) {
        http_response_code(400);
        echo json_encode(["message" => "Supplier ID is required for update"]);
        exit();
    }

    if ($db instanceof PDO) {
        $stmt = $db->prepare("UPDATE supplier SET supplier_name = ?, contact_person = ?, phone = ?, email = ?, address = ?, payment_term = ?, status = ? WHERE supplier_id = ?");
        $success = $stmt->execute([$supplier_name, $contact_person, $phone, $email, $address, $payment_term, $status, $id]);
        echo json_encode(["success" => $success]);
    } else if ($db instanceof mysqli) {
        $id_val  = intval($id);
        $name    = $db->real_escape_string($supplier_name ?? '');
        $contact = $db->real_escape_string($contact_person ?? '');
        $ph      = $db->real_escape_string($phone ?? '');
        $em      = $db->real_escape_string($email ?? '');
        $addr    = $db->real_escape_string($address ?? '');
        $pay     = $db->real_escape_string($payment_term ?? '');
        $st      = $db->real_escape_string($status);

        $sql = "UPDATE supplier SET supplier_name='$name', contact_person='$contact', phone='$ph', email='$em', address='$addr', payment_term='$pay', status='$st' WHERE supplier_id=$id_val";
        $success = $db->query($sql);
        echo json_encode(["success" => $success]);
    }
    exit();
}

/* --- DELETE REQUEST --- */
if ($method === 'DELETE') {
    $id = $_GET['supplier_id'] ?? $_GET['id'] ?? $input['supplier_id'] ?? $input['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(["message" => "Supplier ID required for delete"]);
        exit();
    }

    if ($db instanceof PDO) {
        $stmt = $db->prepare("DELETE FROM supplier WHERE supplier_id = ?");
        $success = $stmt->execute([$id]);
        echo json_encode(["success" => $success]);
    } else if ($db instanceof mysqli) {
        $id_val = intval($id);
        $success = $db->query("DELETE FROM supplier WHERE supplier_id = $id_val");
        echo json_encode(["success" => $success]);
    }
    exit();
}

http_response_code(405);
echo json_encode(["message" => "Method not allowed"]);