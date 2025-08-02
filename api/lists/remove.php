<?php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/jwtUtils.php';


header('Content-Type: application/json');

// Get and decode JSON request body
$data = json_decode(file_get_contents("php://input"), true);
$listName = trim($data['name'] ?? '');

// Check if list name is provided
if (empty($listName)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'List name is required']);
    exit;
}

if ($listName === 'Standard') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cannot delete the Standard list']);
    exit;
}

// Get Authorization header
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authorization header missing or invalid']);
    exit;
}

$jwt = substr($authHeader, 7);

// Validate token and get user ID
$uid = validateJWT($jwt);

if (!$uid) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid or expired token']);
    exit;
}

try {
    $pdo = getDatabaseConnection();

    // Delete list by name and user ID
    $stmt = $pdo->prepare("DELETE FROM lists WHERE user_id = ? AND name = ?");
    $stmt->execute([$uid, $listName]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'List deleted.']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'List not found']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
