<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

use App\Scraper;

// get manxaUrl from query parameter
$manxaUrl = isset($_GET['manxa_url']) ? $_GET['manxa_url'] : null;

if ($manxaUrl === null) {
    echo json_encode(["success" => false, "error" => "manxa_url parameter is required."]);
    exit;
}

$data = Scraper::getManxa($manxaUrl);
echo json_encode(["success" => true, "data" => $data]);