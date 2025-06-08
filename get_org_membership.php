<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
    exit;
}

$user_id = intval($data['user_id']);

try {
    $pdo = new PDO("mysql:host=localhost;dbname=slsu_community;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT o.org_name, m.role
        FROM org_members m
        JOIN organization_profiles o ON m.org_id = o.org_id
        WHERE m.user_id = ? AND m.status = 'active'
    ");
    $stmt->execute([$user_id]);
    $memberships = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($memberships && count($memberships) > 0) {
        echo json_encode([
            'status' => 'success',
            'is_member' => true,
            'organizations' => $memberships
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'is_member' => false,
            'organizations' => []
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB error: ' . $e->getMessage()]);
}
