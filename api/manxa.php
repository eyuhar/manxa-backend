<?php

require_once __DIR__ . '/../vendor/autoload.php';


header('Content-Type: application/json');

use App\Scraper;

// get manxaUrl from query parameter
$manxaUrl = isset($_GET['manxa_url']) ? $_GET['manxa_url'] : null;

if (!$manxaUrl) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Missing 'manxa_url' parameter."
    ]);
    exit;
}

//call scraper
$data = Scraper::getManxa($manxaUrl);

// Handle scraper error
if (isset($data['error'])) {
    http_response_code(502);
    echo json_encode([
        "success" => false,
        "error" => $data['error']
    ]);
    exit;
}

// Success
echo json_encode([
    "success" => true,
    "data" => $data
]);
