<?php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/jwtUtils.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

// Extract and validate manxa title and URL
$title = trim($data['title'] ?? '');
$manxaUrl = trim($data['manxa_url'] ?? '');

if (empty($title) || empty($manxaUrl)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Title and manxa_url are required']);
    exit;
}

// Extract optional list name, default to "Favorites"
$listName = trim($data['list_name'] ?? 'Favorites');

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

try {
    $pdo = getDatabaseConnection();

    // Get list ID based on name and user
    $stmt = $pdo->prepare("SELECT id FROM lists WHERE user_id = ? AND name = ?");
    $stmt->execute([$uid, $listName]);
    $list = $stmt->fetch();

    if (!$list) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'List not found']);
        exit;
    }

    $listId = $list['id'];

    // Check for duplicates
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND list_id = ? AND manxa_url = ?");
    $stmt->execute([$uid, $listId, $manxaUrl]);

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Manga already exists in this list']);
        exit;
    }

    // Insert new favorite
    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, list_id, title, manxa_url) VALUES (?, ?, ?, ?)");
    $stmt->execute([$uid, $listId, $title, $manxaUrl]);

    echo json_encode(['success' => true, 'message' => 'Manga added to favorites']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
