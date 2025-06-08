<?php
header('Content-Type: application/json');
// Database connection
$host = 'localhost';
$db = 'slsu_community';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("
        SELECT p.content, p.image_url, p.created_at, u.username
        FROM posts p
        JOIN users u ON p.user_id = u.user_id
        ORDER BY p.created_at DESC
    ");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'posts' => $posts]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB error: ' . $e->getMessage()]);
}
