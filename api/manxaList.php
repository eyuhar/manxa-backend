<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

use App\Scraper;

// get page from query parameter
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

try {
    $data = Scraper::getManxaList($page);
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}