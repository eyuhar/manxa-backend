<?php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/jwtUtils.php';


header('Content-Type: application/json');

// Read query parameter: manxa_url
$manxaUrl = $_GET['manxa_url'] ?? '';

// Validate input
if (empty($manxaUrl)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'manxa_url is required']);
    exit;
}

// Read JWT from the Authorization header
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

    $stmt = $pdo->prepare("SELECT chapter_url FROM chapter_progress WHERE user_id = ? AND manxa_url = ?");
    $stmt->execute([$uid, $manxaUrl]);
    $chapters = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['success' => true, 'read_chapters' => $chapters]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
