<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/init.php';

use App\Scraper;

// Check if a URL is provided
$url = $_GET['url'] ?? null;

if (!$url) {
    http_response_code(400);
    echo "Missing 'url' parameter.";
    exit;
}

// Fetch the image data
$imageData = Scraper::getImage($url);

// Dynamically detect MIME type:
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->buffer($imageData) ?: 'application/octet-stream';

// Set MIME type
header("Content-Type: $mimeType");

echo $imageData;