<?php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/jwtUtils.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

// Accept either a single object or an array of objects
$items = [];
if (isset($data[0])) {
    $items = $data;
} elseif (isset($data['title']) && isset($data['manxa_url'])) {
    $items[] = [
        'title' => $data['title'],
        'manxa_url' => $data['manxa_url'],
        'list_name' => $data['list_name'] ?? 'Standard'
    ];
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input format']);
    exit;
}

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
    $results = [];
    $allSuccess = true;
    $allFailed = true;

    foreach ($items as $item) {
        $title = trim($item['title'] ?? '');
        $manxaUrl = trim($item['manxa_url'] ?? '');
        $listName = trim($item['list_name'] ?? 'Standard');

        if (empty($title) || empty($manxaUrl)) {
            $results[] = [
                'title' => $title,
                'manxa_url' => $manxaUrl,
                'success' => false,
                'status' => 400,
                'error' => 'Title and manxa_url are required'
            ];
            $allSuccess = false;
            continue;
        }

        // Get list ID based on name and user
        $stmt = $pdo->prepare("SELECT id FROM lists WHERE user_id = ? AND name = ?");
        $stmt->execute([$uid, $listName]);
        $list = $stmt->fetch();

        if (!$list) {
            $results[] = [
                'title' => $title,
                'manxa_url' => $manxaUrl,
                'success' => false,
                'status' => 404,
                'error' => 'List not found'
            ];
            $allSuccess = false;
            continue;
        }

        $listId = $list['id'];

        // Check for duplicates
        $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND list_id = ? AND manxa_url = ?");
        $stmt->execute([$uid, $listId, $manxaUrl]);

        if ($stmt->fetch()) {
            $results[] = [
                'title' => $title,
                'manxa_url' => $manxaUrl,
                'success' => false,
                'status' => 409,
                'error' => 'Manxa already exists in this list'
            ];
            $allSuccess = false;
            continue;
        }

        // Insert new favorite
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, list_id, title, manxa_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$uid, $listId, $title, $manxaUrl]);

        $results[] = [
            'title' => $title,
            'manxa_url' => $manxaUrl,
            'success' => true,
            'status' => 201,
            'message' => 'Manxa added to ' . $listName . '.'
        ];
        $allFailed = false;
    }

    // Set HTTP status code for the whole response
    if ($allSuccess) {
        http_response_code(201); // All created
    } elseif ($allFailed) {
        http_response_code(400); // All failed (bad request)
    } else {
        http_response_code(207); // Multi-Status (partial success)
    }

    echo json_encode(['results' => $results]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
