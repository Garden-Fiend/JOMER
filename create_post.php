<?php
header('Content-Type: application/json');

$user_id = $_POST['user_id'] ?? null;
$content = $_POST['content'] ?? '';
$image_url = null;

if (!$user_id || (empty($content) && !isset($_FILES['file']))) {
    echo json_encode(['status' => 'error', 'message' => 'Missing content or image.']);
    exit;
}
// Database connection
$host = 'localhost';
$db = 'slsu_community';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle file upload
    if (isset($_FILES['file'])) {
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . "." . $ext;
        $uploadPath = "uploads/" . $filename;

        if (!is_dir("uploads")) {
            mkdir("uploads");
        }

        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath)) {
            $image_url = $uploadPath;
        }
    }

    // Insert post
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image_url) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $content, $image_url]);

    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB error: ' . $e->getMessage()]);
}
