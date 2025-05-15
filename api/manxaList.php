<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once __DIR__ . '/../vendor/autoload.php';

use App\Scraper;

// get page from query parameter
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$data = Scraper::getManxaList($page);
echo json_encode($data);