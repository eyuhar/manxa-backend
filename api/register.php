<?php
// Load the database connection function
require_once __DIR__ . '/../src/db.php';

// Set response content type to JSON
header("Content-Type: application/json");

// Receive JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Check if email & password were provided
if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(["error" => "Email and password are required."]);
    exit;
}

// Extract email and password from the request
$email = $data['email'] ?? null;
$password = $data['password'] ?? null;

try {
    // Get a database connection
    $pdo = getDatabaseConnection();

    // Check if the email is already registered
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Email is already registered.']);
        exit;
    }

    // Hash the password securely
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the database
    $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->execute([$email, $hashedPassword]);

    // Respond with success
    echo json_encode(['message' => 'User registered successfully.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
