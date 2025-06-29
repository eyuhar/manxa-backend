<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/init.php';

header('Content-Type: application/json');

use App\Scraper;

// Get query and page from query parameters
$query = isset($_GET['query']) ? $_GET['query'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if (!$query) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Missing 'query' parameter."
    ]);
    exit;
}

$data = Scraper::getSearchResults($query, $page);

if (isset($data['error'])) {
    http_response_code(502);
    echo json_encode([
        "success" => false,
        "error" => $data['error']
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);