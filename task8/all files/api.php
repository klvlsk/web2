<?php
header('Content-Type: application/json');
require_once 'DatabaseRepository.php';
require_once 'Validator.php';

session_start();

$db = new DatabaseRepository();
$method = $_SERVER['REQUEST_METHOD'];
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

// Единая точка входа для API
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', $path);
$resource = $pathParts[count($pathParts) - 2]; // api.php/{resource}
$resourceId = $pathParts[count($pathParts) - 1] ?? null;

// Определяем тип контента и парсим данные
if ($method === 'POST' || $method === 'PUT') {
    $input = file_get_contents('php://input');
    
    if (strpos($contentType, 'application/xml') !== false) {
        $data = simplexml_load_string($input);
        if ($data === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid XML data']);
            exit;
        }
        $data = json_decode(json_encode($data), true);
    } else {
        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
            exit;
        }
    }
}

switch ($method) {
    case 'GET':
        handleGetRequest($db, $resourceId);
        break;
    case 'POST':
        handlePostRequest($db, $data);
        break;
    case 'PUT':
        handlePutRequest($db, $resourceId, $data);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

function handleGetRequest(DatabaseRepository $db, $userId) {
    if (empty($userId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        return;
    }

    $user = $db->getUser($userId);
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }

    $user['languages'] = $db->getUserLanguages($user['id']);
    unset($user['pass']);
    
    echo json_encode([
        'success' => true,
        'data' => $user
    ]);
}

function handlePostRequest(DatabaseRepository $db, $data) {
    $errors = Validator::validateUserForm($data);
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
        return;
    }
    
    try {
        $result = $db->createUser($data);
        
        echo json_encode([
            'success' => true,
            'message' => 'User created successfully',
            'login' => $result['login'],
            'password' => $result['pass'],
            'profile_url' => 'index.php?login=' . urlencode($result['login'])
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error creating user: ' . $e->getMessage()
        ]);
    }
}

function handlePutRequest(DatabaseRepository $db, $userId, $data) {
    if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        return;
    }
    
    $user = $db->checkUserCredentials($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    if (!$user || $user['id'] != $userId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        return;
    }
    
    $errors = Validator::validateUserForm($data);
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
        return;
    }
    
    try {
        $db->updateUser($userId, $data);
        
        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error updating user: ' . $e->getMessage()
        ]);
    }
}