<?php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/jwtUtils.php';

header('Content-Type: application/json');

// Get and decode JSON request body
$data = json_decode(file_get_contents("php://input"), true);

$items = [];
if (isset($data[0])) {
    $items = $data;
} elseif (isset($data['manxa_url']) && isset($data['chapter_url'])) {
    $items[] = [
        'manxa_url' => $data['manxa_url'],
        'chapter_url' => $data['chapter_url']
    ];
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input format']);
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
    $results = [];
    $allSuccess = true;
    $allFailed = true;

    foreach ($items as $item) {
        $manxaUrl = trim($item['manxa_url'] ?? '');
        $chapterUrl = trim($item['chapter_url'] ?? '');

        if (empty($manxaUrl) || empty($chapterUrl)) {
            $results[] = [
                'manxa_url' => $manxaUrl,
                'chapter_url' => $chapterUrl,
                'success' => false,
                'status' => 400,
                'error' => 'manxa_url and chapter_url are required'
            ];
            $allSuccess = false;
            continue;
        }

        // Delete the record marking the chapter as read
        $stmt = $pdo->prepare("DELETE FROM chapter_progress WHERE user_id = ? AND manxa_url = ? AND chapter_url = ?");
        $stmt->execute([$uid, $manxaUrl, $chapterUrl]);

        if ($stmt->rowCount() === 0) {
            $results[] = [
                'manxa_url' => $manxaUrl,
                'chapter_url' => $chapterUrl,
                'success' => false,
                'status' => 404,
                'error' => 'Chapter not found or not marked as read'
            ];
            $allSuccess = false;
        } else {
            $results[] = [
                'manxa_url' => $manxaUrl,
                'chapter_url' => $chapterUrl,
                'success' => true,
                'status' => 200,
                'message' => 'Chapter unmarked as read.'
            ];
            $allFailed = false;
        }
    }

    // Set HTTP status code for the whole response
    if ($allSuccess) {
        http_response_code(200); // All deleted
    } elseif ($allFailed) {
        http_response_code(400); // All failed (bad request)
    } else {
        http_response_code(207); // Multi-Status
    }

    echo json_encode(['results' => $results]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
