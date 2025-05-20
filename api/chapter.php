<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once __DIR__ . '/../vendor/autoload.php';

use App\Scraper;

// get chapter from query parameter
$chapter = isset($_GET['chapter']) ? $_GET['chapter'] : null;
if ($chapter === null) {
    echo json_encode(["success" => false, "error" => "Chapter parameter is required."]);
    exit;
}

$images = Scraper::getChapter($chapter);
echo json_encode(["success" => true, "data" => $images]);