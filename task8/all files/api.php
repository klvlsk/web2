<?php
header('Content-Type: application/json');
require_once 'DatabaseRepository.php';
require_once 'Validator.php';

session_start();

$db = new DatabaseRepository();
$method = $_SERVER['REQUEST_METHOD'];

// Единая точка входа для API
switch ($method) {
    case 'GET':
        handleGetRequest($db);
        break;
    case 'POST':
        handlePostRequest($db);
        break;
    case 'PUT':
        handlePutRequest($db);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

function handleGetRequest(DatabaseRepository $db) {
    if (empty($_GET['login'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Login parameter is required']);
        return;
    }

    $user = $db->getUserByLogin($_GET['login']);
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }

    $user['languages'] = $db->getUserLanguages($user['id']);
    unset($user['pass']); // Не возвращаем хэш пароля
    
    echo json_encode([
        'success' => true,
        'data' => $user
    ]);
}

function handlePostRequest(DatabaseRepository $db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
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
        $result = $db->createUser($data);
        
        echo json_encode([
            'success' => true,
            'message' => 'User created successfully',
            'login' => $result['login'],
            'password' => $result['pass'],
            'profile_url' => 'login.php?login=' . urlencode($result['login'])
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error creating user: ' . $e->getMessage()
        ]);
    }
}

function handlePutRequest(DatabaseRepository $db) {
    if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        return;
    }
    
    $user = $db->checkUserCredentials($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    if (!$user) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
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
        $db->updateUser($user['id'], $data);
        
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