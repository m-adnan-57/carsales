<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { 
    http_response_code(200); 
    exit; 
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/api/cars.php';
require_once __DIR__ . '/api/auth.php';
require_once __DIR__ . '/api/favourites.php';
require_once __DIR__ . '/api/messages.php';

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = str_replace('/api', '', $uri);
$method = $_SERVER['REQUEST_METHOD'];
$parts  = explode('/', trim($uri, '/'));
$resource = $parts[0] ?? '';
$id       = $parts[1] ?? null;

switch ($resource) {
    case 'cars':
        handleCars($method, $id);
        break;
    case 'login':
        handleLogin();
        break;
    case 'register':
        handleRegister();
        break;
    case 'favourites':
        handleFavourites($method, $id);
        break;
    case 'messages':
        handleMessages($method, $id);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
}