<?php
require_once __DIR__ . '/../src/jwtUtils.php';
require_once __DIR__ . '/../src/db.php';

// 1. Get Authorization header
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['error' => 'Authorization header missing or invalid']);
    exit;
}

$jwt = substr($authHeader, 7); // Remove "Bearer "

// 2. Validate JWT
$payload = validateJWT($jwt);
if (!$payload || !isset($payload['uid'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or expired token']);
    exit;
}

// 3. Get user data from database
$pdo = getDatabaseConnection();
$stmt = $pdo->prepare("SELECT id, email, created_at FROM users WHERE id = ?");
$stmt->execute([$payload['uid']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

// 4. Return user profile
header('Content-Type: application/json');
echo json_encode($user);
