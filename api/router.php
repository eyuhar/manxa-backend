<?php

require_once __DIR__ . '/init.php';

// Current request info
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Only paths starting with /api/... are allowed
if (!str_starts_with($uri, '/api/')) {
    http_response_code(404);
    echo json_encode(['error' => 'Invalid API path']);
    exit;
}

$path = substr($uri, 4); // removes exactly "/api"
$path = rtrim($path, '/');
$basePath = __DIR__;


// Routing logic
switch ($path) {
    case '/favorites':
        routeTo("$basePath/favorites/" . methodToFile($method));
        break;

    case '/lists':
        routeTo("$basePath/lists/" . methodToFile($method));
        break;

    case '/chapter-progress':
        switch ($method) {
            case 'GET':
                routeTo("$basePath/chapter-progress/getReadChapters.php");
                break;
            case 'POST':
                routeTo("$basePath/chapter-progress/markRead.php");
                break;
            case 'DELETE':
                routeTo("$basePath/chapter-progress/unmarkRead.php");
                break;
            default:
                sendMethodNotAllowed();
        }
        break;

    case '/profile':
        if ($method === 'GET') {
            routeTo("$basePath/getProfile.php");
        } else {
            sendMethodNotAllowed();
        }
        break;

    case '/login':
        if ($method === 'POST') {
            routeTo("$basePath/login.php");
        } else {
            sendMethodNotAllowed();
        }
        break;

    case '/register':
        if ($method === 'POST') {
            routeTo("$basePath/register.php");
        } else {
            sendMethodNotAllowed();
        }
        break;

    case '/manxas':
        if ($method === 'GET') {
            // If query param "query" is present → search.php
            if (isset($_GET['query'])) {
                routeTo("$basePath/search.php");
            } else {
                routeTo("$basePath/manxaList.php");
            }
        } else {
            sendMethodNotAllowed();
        }
        break;

    case '/manxa':
        if ($method === 'GET') {
            routeTo("$basePath/manxa.php");
        } else {
            sendMethodNotAllowed();
        }
        break;

    case '/history':
        if ($method === 'GET') {
            routeTo("$basePath/history.php");
        } else {
            sendMethodNotAllowed();
        }
        break;

    case '/chapter':
        if ($method === 'GET') {
            routeTo("$basePath/chapter.php");
        } else {
            sendMethodNotAllowed();
        }
        break;

    case '/image-proxy':
        if ($method === 'GET') {
            routeTo("$basePath/imageProxy.php");
        } else {
            sendMethodNotAllowed();
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
}

// === Helper functions ===

function routeTo(string $filePath): void
{
    if (file_exists($filePath)) {
        require_once $filePath;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint file not found']);
    }
}

function sendMethodNotAllowed(): void
{
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

function methodToFile(string $method): string
{
    return match ($method) {
        'GET' => 'get.php',
        'POST' => 'add.php',
        'DELETE' => 'remove.php',
        'PUT' => 'rename.php',
        default => null,
    };
}
