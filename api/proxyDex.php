<?php

// Allowed remote API domains
$allowed_domains = ['api.mangadex.org', 'uploads.mangadex.org', 'mangadex.org'];

// Read API URL from query string
if (!isset($_GET['url'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing url parameter']);
    exit;
}

$url = $_GET['url'];

// Parse and validate domain
$parsed = parse_url($url);

$hostname = $parsed['host'];

// Check if the hostname is explicitly allowed
$isAllowed = in_array($hostname, $allowed_domains);

// Also allow any subdomain of mangadex.network
if (!$isAllowed && str_ends_with($hostname, '.mangadex.network')) {
    $isAllowed = true;
}

if (!$isAllowed) {
    http_response_code(403);
    echo json_encode(['error' => 'Domain not allowed']);
    exit;
}

// Initialize cURL request
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, true);

// Set a proper User-Agent (required by MangaDex)
curl_setopt($ch, CURLOPT_USERAGENT, 'ManxaFrontend/1.0 (https://manxa.vercel.app/)');

// Keep SSL verification ON (MangaDex requires valid TLS)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

// Execute
$response = curl_exec($ch);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$body = substr($response, $header_size);
curl_close($ch);

// Set cache headers – 1 hour
header("Cache-Control: public, max-age=3600");
header("Expires: " . gmdate('D, d M Y H:i:s', time() + 3600) . " GMT");
// Return same status and JSON content
header("Content-Type: application/json");
http_response_code($http_status);
echo $body;
