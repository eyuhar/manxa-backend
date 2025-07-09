<?php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/jwtUtils.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);


$items = [];
if (isset($data[0])) {
    $items = $data;
} elseif (isset($data['manxa_url'])) {
    $items[] = [
        'manxa_url' => $data['manxa_url'],
        'list_name' => $data['list_name'] ?? 'Standard'
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
        $listName = trim($item['list_name'] ?? 'Standard');

        if (empty($manxaUrl)) {
            $results[] = [
                'manxa_url' => $manxaUrl,
                'list_name' => $listName,
                'success' => false,
                'status' => 400,
                'error' => 'manxa_url is required'
            ];
            $allSuccess = false;
            continue;
        }

        // Retrieve list_id based on name and user_id
        $stmt = $pdo->prepare("SELECT id FROM lists WHERE user_id = ? AND name = ?");
        $stmt->execute([$uid, $listName]);
        $list = $stmt->fetch();

        if (!$list) {
            $results[] = [
                'manxa_url' => $manxaUrl,
                'list_name' => $listName,
                'success' => false,
                'status' => 404,
                'error' => 'List not found'
            ];
            $allSuccess = false;
            continue;
        }

        $listId = $list['id'];

        // Remove manxa from favorites
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND list_id = ? AND manxa_url = ?");
        $stmt->execute([$uid, $listId, $manxaUrl]);

        if ($stmt->rowCount() === 0) {
            $results[] = [
                'manxa_url' => $manxaUrl,
                'list_name' => $listName,
                'success' => false,
                'status' => 404,
                'error' => 'Manxa not found in ' . $listName . '.'
            ];
            $allSuccess = false;
        } else {
            $results[] = [
                'manxa_url' => $manxaUrl,
                'list_name' => $listName,
                'success' => true,
                'status' => 200,
                'message' => 'Manxa removed from ' . $listName . '.'
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
        http_response_code(207); // Multi-Status (partial success)
    }

    echo json_encode(['results' => $results]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
