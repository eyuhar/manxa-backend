<?php
// Connection to the database
require_once __DIR__ . '/../src/db.php';

// Receive JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Check if email & password were provided
if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(["error" => "Email and password are required."]);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert into the database
try {
    $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->execute([$email, $hashedPassword]);

    echo json_encode(["message" => "User registered successfully."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Registration failed: " . $e->getMessage()]);
}
