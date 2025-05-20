<?php

require_once __DIR__ . '/../src/jwtUtils.php';
// Load the database connection function
require_once __DIR__ . '/../src/db.php';

// Set response header to JSON
header("Content-Type: application/json");

// Read JSON input from request body
$data = json_decode(file_get_contents("php://input"), true);

// Extract email and password from request
$email = $data['email'] ?? null;
$password = $data['password'] ?? null;

// Check if required fields are present
if (!$email || !$password) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Email and password are required.'
    ]);
    exit;
}

try {
    // Connect to the database
    $pdo = getDatabaseConnection();

    // Fetch user by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password
    if ($user && password_verify($password, $user['password'])) {
        // Generate JWT token
        $token = generateJWT($user['id']);

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid email or password.'
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}