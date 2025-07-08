<?php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/jwtUtils.php';
require_once __DIR__ . '/../init.php';

// Set response content type to JSON
header('Content-Type: application/json');

// Get POST data and decode JSON payload
$data = json_decode(file_get_contents("php://input"), true);
$listName = trim($data['name'] ?? '');

// Check if list name is empty
if (empty($listName)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'List name is required']);
    exit;
}

// Get Authorization header
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authorization header missing or invalid']);
    exit;
}

$jwt = substr($authHeader, 7); // Remove "Bearer "

// Validate the JWT token and get user id (uid)
$uid = validateJWT($jwt);

if (!$uid) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid or expired token']);
    exit;
}

try {
    // Get PDO database connection
    $pdo = getDatabaseConnection();

    // Check if a list with the same name already exists for this user
    $stmt = $pdo->prepare("SELECT id FROM lists WHERE user_id = ? AND name = ?");
    $stmt->execute([$uid, $listName]);

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'List with this name already exists']);
        exit;
    }

    // Insert new list record into database
    $stmt = $pdo->prepare("INSERT INTO lists (user_id, name) VALUES (?, ?)");
    $stmt->execute([$uid, $listName]);

    echo json_encode(['success' => true, 'message' => 'List created.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
