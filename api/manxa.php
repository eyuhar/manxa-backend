<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once __DIR__ . '/../vendor/autoload.php';

use App\Scraper;

// get title from query parameter
$title = isset($_GET['title']) ? $_GET['title'] : null;
if ($title === null) {
    echo json_encode(["success" => false, "error" => "Title parameter is required."]);
    exit;
}

$data = Scraper::getManxa($title);
echo json_encode(["success" => true, "data" => $data]);