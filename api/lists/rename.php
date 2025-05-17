<?php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/jwtUtils.php';

header('Content-Type: application/json');

// Parse JSON body
$data = json_decode(file_get_contents("php://input"), true);
$oldName = trim($data['old_name'] ?? '');
$newName = trim($data['new_name'] ?? '');

// Validate input
if (empty($oldName) || empty($newName)) {
    http_response_code(400);
    echo json_encode(['error' => 'Both old and new list names are required']);
    exit;
}

// Extract and validate JWT
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['error' => 'Authorization header missing or invalid']);
    exit;
}
$jwt = substr($authHeader, 7);
$uid = validateJWT($jwt);

if (!$uid) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or expired token']);
    exit;
}

try {
    $pdo = getDatabaseConnection();

    // Check if the list exists for the user
    $stmt = $pdo->prepare("SELECT id FROM lists WHERE user_id = ? AND name = ?");
    $stmt->execute([$uid, $oldName]);
    $list = $stmt->fetch();

    if (!$list) {
        http_response_code(404);
        echo json_encode(['error' => 'List not found']);
        exit;
    }

    // Check for name conflict
    $stmt = $pdo->prepare("SELECT id FROM lists WHERE user_id = ? AND name = ?");
    $stmt->execute([$uid, $newName]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'A list with the new name already exists']);
        exit;
    }

    // Update list name
    $stmt = $pdo->prepare("UPDATE lists SET name = ? WHERE user_id = ? AND name = ?");
    $stmt->execute([$newName, $uid, $oldName]);

    echo json_encode(['success' => true, 'message' => 'List renamed']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
