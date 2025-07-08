<?php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/jwtUtils.php';
require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

// Get and decode the incoming JSON data
$data = json_decode(file_get_contents("php://input"), true);
$manxaUrl = trim($data['manxa_url'] ?? '');
$chapterUrl = trim($data['chapter_url'] ?? '');

// Validate input
if (empty($manxaUrl) || empty($chapterUrl)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'manxa_url and chapter_url are required']);
    exit;
}

// Extract and validate JWT
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

    // Check if this chapter was already marked as read
    $stmt = $pdo->prepare("SELECT id FROM chapter_progress WHERE user_id = ? AND manxa_url = ? AND chapter_url = ?");
    $stmt->execute([$uid, $manxaUrl, $chapterUrl]);

    if ($stmt->fetch()) {
        echo json_encode(['success' => true, 'message' => 'Already marked as read']);
        exit;
    }

    // Insert new record
    $stmt = $pdo->prepare("INSERT INTO chapter_progress (user_id, manxa_url, chapter_url) VALUES (?, ?, ?)");
    $stmt->execute([$uid, $manxaUrl, $chapterUrl]);

    echo json_encode(['success' => true, 'message' => 'Chapter marked as read.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
