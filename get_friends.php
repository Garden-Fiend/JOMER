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
        JOIN users u ON (u.user_id = f.sender_id OR u.user_id = f.receiver_id)
        WHERE (f.sender_id = :id OR f.receiver_id = :id)
          AND f.status = 'accepted'
          AND u.user_id != :id
    ");
    $stmt->execute(['id' => $user_id]);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'friends' => $friends]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
