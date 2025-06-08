<?php
header('Content-Type: application/json');

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing user_id']);
    exit;
}

$host = 'localhost';
$db = 'slsu_community';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT u.user_id, u.username
        FROM friends f
        JOIN users u ON f.sender_id = u.user_id
        WHERE f.receiver_id = ? AND f.status = 'pending'
    ");
    $stmt->execute([$user_id]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'requests' => $requests]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
