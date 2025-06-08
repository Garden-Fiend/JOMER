<?php
// Set response header
header('Content-Type: application/json');

// Read and decode JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Basic validation
if (!isset($data['email'], $data['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing email or password']);
    exit;
}

$email = trim($data['email']);
$password = trim($data['password']);

// Database connection
$host = 'localhost';
$db = 'slsu_community';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch user by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Success
        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'account_type' => $user['account_type'],
            'user_id' => $user['user_id'], // Optional
            'username' => $user['username']
        ]);
    } else {
        // Invalid credentials
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
