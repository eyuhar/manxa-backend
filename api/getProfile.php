<?php
require_once __DIR__ . '/../src/jwtUtils.php';
require_once __DIR__ . '/../src/db.php';

header('Content-Type: application/json');

// Get Authorization header
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authorization header missing or invalid']);
    exit;
}

$jwt = substr($authHeader, 7); // Remove "Bearer "

// Validate JWT
$uid = validateJWT($jwt);
if (!$uid) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid or expired token']);
    exit;
}

// Get user data from database
$pdo = getDatabaseConnection();
$stmt = $pdo->prepare("SELECT id, email, created_at, user_name FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

// Return user profile
echo json_encode(['success' => true, 'data' => $user]);
