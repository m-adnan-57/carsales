<?php

define('JWT_SECRET', 'mysecretkey123456');

function generateJWT(array $payload): string {
    $header  = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64url_encode(json_encode($payload));
    $sig     = base64url_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    return "$header.$payload.$sig";
}

function verifyJWT(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    [$header, $payload, $sig] = $parts;
    $expected = base64url_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    if (!hash_equals($expected, $sig)) return null;
    $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
    if (!$data || $data['exp'] < time()) return null;
    return $data;
}

function requireAuth(): array {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (!preg_match('/Bearer\s+(.+)/', $authHeader, $m)) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorised']);
        exit;
    }
    $user = verifyJWT(trim($m[1]));
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired token']);
        exit;
    }
    return $user;
}

function requireAdmin(): array {
    $user = requireAuth();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        exit;
    }
    return $user;
}

function base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}