<?php
header('Content-Type: application/json');

$sender_id = $_POST['sender_id'] ?? null;
$receiver_id = $_POST['receiver_id'] ?? null;
$action = $_POST['action'] ?? '';

if (!$sender_id || !$receiver_id || !in_array($action, ['accept', 'reject'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$status = $action === 'accept' ? 'accepted' : 'rejected';

$host = 'localhost';
$db = 'slsu_community';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        UPDATE friends
        SET status = ?
        WHERE sender_id = ? AND receiver_id = ? AND status = 'pending'
    ");
    $stmt->execute([$status, $sender_id, $receiver_id]);

    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
