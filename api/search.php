<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

use App\Scraper;

// get manxaUrl from query parameter
$query = isset($_GET['query']) ? $_GET['query'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($query === null) {
    echo json_encode(["success" => false, "error" => "query parameter is required."]);
    exit;
}

$data = Scraper::getSearchResults($query, $page);
echo json_encode(["success" => true, "data" => $data]);