<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/init.php';

header('Content-Type: application/json');

use App\Scraper;

// get page from query parameter (default = 1)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$data = Scraper::getManxaList($page);

// Handle scraper error
if (isset($data['error'])) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'error' => $data['error']
    ]);
    exit;
}

// Success
echo json_encode([
    'success' => true,
    'data' => $data
]);