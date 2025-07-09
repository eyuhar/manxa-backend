<?php

require_once __DIR__ . '/../vendor/autoload.php';


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

if ($imageData === false) {
    http_response_code(502); // Bad Gateway = external problem
    echo "Failed to fetch image from external source.";
    exit;
}

// Dynamically detect MIME type:
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->buffer($imageData) ?: 'application/octet-stream';

// Set MIME type
header("Content-Type: $mimeType");
header("Content-Length: " . strlen($imageData));

echo $imageData;
