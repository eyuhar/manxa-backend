<?php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/jwtUtils.php';


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

try {
    $pdo = getDatabaseConnection();

    // Get all lists for this user
    $stmt = $pdo->prepare("SELECT name, created_at FROM lists WHERE user_id = ?");
    $stmt->execute([$uid]);
    $lists = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'lists' => $lists]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
