<?php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/jwtUtils.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$manxaUrl = trim($data['manxa_url'] ?? '');
$listName = trim($data['list_name'] ?? 'Favorites');

if (empty($manxaUrl)) {
    http_response_code(400);
    echo json_encode(['error' => 'manxa_url is required']);
    exit;
}

// Read JWT from the Authorization header
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

    // Retrieve list_id based on name and user_id
    $stmt = $pdo->prepare("SELECT id FROM lists WHERE user_id = ? AND name = ?");
    $stmt->execute([$uid, $listName]);
    $list = $stmt->fetch();

    if (!$list) {
        http_response_code(404);
        echo json_encode(['error' => 'List not found']);
        exit;
    }

    $listId = $list['id'];

    // Remove manxa from favorites
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND list_id = ? AND manxa_url = ?");
    $stmt->execute([$uid, $listId, $manxaUrl]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Manga not found in favorites']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Manga removed from favorites']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
