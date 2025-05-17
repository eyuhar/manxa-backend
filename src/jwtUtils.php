<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$JWT_SECRET = $_ENV['JWT_SECRET'];

// Generate token
function generateJWT($userId) {
    global $JWT_SECRET;
    $payload = [
        'uid' => $userId,
        'iat' => time(),
        'exp' => time() + 3600 // Valid for 1 hour
    ];

    return JWT::encode($payload, $JWT_SECRET, 'HS256');
}

// Validate token
function validateJWT($token) {
    global $JWT_SECRET;
    try {
        $decoded = JWT::decode($token, new Key($JWT_SECRET, 'HS256'));
        return $decoded->uid ?? null;
    } catch (Exception $e) {
        return null;
    }
}