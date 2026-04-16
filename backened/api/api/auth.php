<?php
// backend/api/auth.php

function handleRegister(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); echo json_encode(['error'=>'Method not allowed']); return;
    }
    $db   = getDB();
    $data = json_decode(file_get_contents('php://input'), true);

    // Validation
    if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
        http_response_code(422);
        echo json_encode(['error' => 'Name, email and password are required']);
        return;
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['error' => 'Invalid email address']);
        return;
    }
    if (strlen($data['password']) < 8) {
        http_response_code(422);
        echo json_encode(['error' => 'Password must be at least 8 characters']);
        return;
    }

    // Check duplicate email
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Email already registered']);
        return;
    }

    $hash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $db->prepare('INSERT INTO users (name,email,password) VALUES (?,?,?)');
    $stmt->execute([$data['name'], $data['email'], $hash]);

    http_response_code(201);
    echo json_encode(['message' => 'Account created successfully']);
}

function handleLogin(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); echo json_encode(['error'=>'Method not allowed']); return;
    }
    $db   = getDB();
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['email']) || empty($data['password'])) {
        http_response_code(422);
        echo json_encode(['error' => 'Email and password are required']);
        return;
    }

    $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($data['password'], $user['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password']);
        return;
    }

    $token = generateJWT([
        'sub'   => $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
        'role'  => $user['role'],
        'iat'   => time(),
        'exp'   => time() + 86400,   // 24 hours
    ]);

    echo json_encode([
        'token' => $token,
        'user'  => [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ]
    ]);
}
