<?php
header("Content-Type: application/json");

$host = 'localhost';
$db = 'slsu_community';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get JSON input and decode
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
    exit;
}

// Check required fields for users table
$requiredUserFields = ['username', 'email', 'password', 'account_type'];
foreach ($requiredUserFields as $field) {
    if (empty($data[$field])) {
        echo json_encode(['status' => 'error', 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Validate account type
$validTypes = ['personal', 'organization'];
if (!in_array($data['account_type'], $validTypes)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid account type']);
    exit;
}

// Check if username or email already exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
$stmt->execute([
    ':username' => $data['username'],
    ':email' => $data['email']
]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Username or email already exists']);
    exit;
}

// Hash password
$hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

// Insert into users table
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, account_type) VALUES (:username, :email, :password, :account_type)");
    $stmt->execute([
        ':username' => $data['username'],
        ':email' => $data['email'],
        ':password' => $hashedPassword,
        ':account_type' => $data['account_type']
    ]);

    $userId = $pdo->lastInsertId();

    // Insert into profile table depending on account type
    if ($data['account_type'] === 'personal') {
        // Required fields for personal profile
        $requiredPersonal = ['firstName', 'lastName']; // adjust keys as needed
        foreach ($requiredPersonal as $field) {
            if (empty($data[$field])) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => "Missing personal profile field: $field"]);
                exit;
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO personal_profiles 
            (user_id, first_name, last_name, bio, profile_picture, department, course, year_level, student_id) 
            VALUES 
            (:user_id, :first_name, :last_name, :bio, :profile_picture, :department, :course, :year_level, :student_id)
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':first_name' => $data['firstName'],
            ':last_name' => $data['lastName'],
            ':bio' => $data['bio'] ?? null,
            ':profile_picture' => $data['profilePicture'] ?? null,
            ':department' => $data['department'] ?? null,
            ':course' => $data['course'] ?? null,
            ':year_level' => $data['yearLevel'] ?? null,
            ':student_id' => $data['studentId'] ?? null
        ]);
    } else {
        // organization profile
        // Required field
        if (empty($data['organizationName'])) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Missing organizationName']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO organization_profiles
            (user_id, org_name, description, logo, department, established_date)
            VALUES
            (:user_id, :org_name, :description, :logo, :department, :established_date)
        ");

        // Convert date if provided, else null
        $establishedDate = !empty($data['establishedDate']) ? $data['establishedDate'] : null;

        $stmt->execute([
            ':user_id' => $userId,
            ':org_name' => $data['organizationName'],
            ':description' => $data['description'] ?? null,
            ':logo' => $data['logo'] ?? null,
            ':department' => $data['college'] ?? null,
            ':established_date' => $establishedDate
        ]);
    }

    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Registration successful']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
