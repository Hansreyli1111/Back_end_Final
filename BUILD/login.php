<?php
include './config/connectdb.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Please enter both username and password."
    ]);
    exit();
}

// 💡 ផ្ទៀងផ្ទាត់ឈ្មោះ Table ក្នុង Database របស់អ្នក ( user ឬ users )
$stmt = $connection->prepare("SELECT user_id, username, password, role_id, status FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // ពិនិត្យ Password (គាំទ្រទាំង Password ដែល Hash ជាមួយ password_hash និង Password ធម្មតា '123')
    $is_valid = password_verify($password, $user['password']) || ($password === $user['password']);

    if ($is_valid) {
        unset($user['password']); // លុប Password ចេញពី Object មុននឹងឆ្លើយតបទៅ Frontend

        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "user" => $user
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Invalid credentials. Please check your username and password and try again."
        ]);
    }
} else {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Invalid credentials. Please check your username and password and try again."
    ]);
}

$stmt->close();
$connection->close();
?>