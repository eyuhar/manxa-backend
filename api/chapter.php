<?php

require_once __DIR__ . '/../vendor/autoload.php';


header('Content-Type: application/json');

use App\Scraper;

$chapterUrl = $_GET['chapter'] ?? null;

if (!$chapterUrl) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Missing 'chapter' parameter."
    ]);
    exit;
}

$data = Scraper::getChapter($chapterUrl);

// Check if an error was returned
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
