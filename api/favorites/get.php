<?php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/jwtUtils.php';
require_once __DIR__ . '/init.php';

header('Content-Type: application/json');

// Get Authorization header
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authorization header missing or invalid']);
    exit;
}

$jwt = substr($authHeader, 7);
$uid = validateJWT($jwt);

if (!$uid) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid or expired token']);
    exit;
}

// Get optional list name from query string
$listName = $_GET['list'] ?? 'Standard';

try {
    $pdo = getDatabaseConnection();

    // Get list ID from name and user
    $stmt = $pdo->prepare("SELECT id FROM lists WHERE user_id = ? AND name = ?");
    $stmt->execute([$uid, $listName]);
    $list = $stmt->fetch();

    if (!$list) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'List not found']);
        exit;
    }

    $listId = $list['id'];

    // Get all favorites from that list
    $stmt = $pdo->prepare("SELECT title, manxa_url, created_at FROM favorites WHERE user_id = ? AND list_id = ?");
    $stmt->execute([$uid, $listId]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'list' => ''.$listName, 'favorites' => $favorites]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
