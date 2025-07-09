<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Generate token
function generateJWT($userId)
{
    $secret = $_ENV['JWT_SECRET'];
    $payload = [
        'uid' => $userId,
        'iat' => time(),
        'exp' => time() + 3600 // Valid for 1 hour
    ];

    return JWT::encode($payload, $secret, 'HS256');
}

// Validate token
function validateJWT($token)
{
    $secret = $_ENV['JWT_SECRET'];
    try {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        return $decoded->uid ?? null;
    } catch (Exception $e) {
        return null;
    }
}
