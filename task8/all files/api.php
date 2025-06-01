<?php
require_once 'DatabaseRepository.php';
require_once 'Validator.php';

session_start();

$db = new DatabaseRepository();
$method = $_SERVER['REQUEST_METHOD'];

// Всегда используем JSON для ответа
header('Content-Type: application/json');

// Парсим входные данные
$input = file_get_contents('php://input');
$data = [];

if ($method === 'POST' || $method === 'PUT') {
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendResponse(400, [
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        exit;
    }
}

// Обработка запросов
switch ($method) {
    case 'GET':
        handleGetRequest($db, $_GET);
        break;
    case 'POST':
        handlePostRequest($db, $data);
        break;
    case 'PUT':
        handlePutRequest($db, $data);
        break;
    default:
        sendResponse(405, [
            'success' => false,
            'message' => 'Method not allowed'
        ]);
        break;
}

function handleGetRequest(DatabaseRepository $db, $params) {
    if (empty($params['login'])) {
        sendResponse(400, [
            'success' => false,
            'message' => 'Login parameter is required'
        ]);
        return;
    }

    $user = $db->getUserByLogin($params['login']);
    if (!$user) {
        sendResponse(404, [
            'success' => false,
            'message' => 'User not found'
        ]);
        return;
    }

    $user['languages'] = $db->getUserLanguages($user['id']);
    unset($user['pass']);
    
    sendResponse(200, [
        'success' => true,
        'data' => $user
    ]);
}

function handlePostRequest(DatabaseRepository $db, $data) {
    $errors = Validator::validateUserForm($data);
    if (!empty($errors)) {
        sendResponse(400, [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
        return;
    }
    
    try {
        $result = $db->createUser($data);
        
        sendResponse(200, [
            'success' => true,
            'message' => 'User created successfully',
            'login' => $result['login'],
            'password' => $result['pass'],
            'profile_url' => 'login.php?login=' . urlencode($result['login']) . '&pass=' . urlencode($result['pass'])
        ]);
    } catch (Exception $e) {
        sendResponse(500, [
            'success' => false,
            'message' => 'Error creating user: ' . $e->getMessage()
        ]);
    }
}

function handlePutRequest(DatabaseRepository $db, $data) {
    if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
        sendResponse(401, [
            'success' => false,
            'message' => 'Authentication required'
        ]);
        return;
    }
    
    $user = $db->checkUserCredentials($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    if (!$user) {
        sendResponse(403, [
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
        return;
    }
    
    $errors = Validator::validateUserForm($data);
    if (!empty($errors)) {
        sendResponse(400, [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
        return;
    }
    
    try {
        $db->updateUser($user['id'], $data);
        
        sendResponse(200, [
            'success' => true,
            'message' => 'User updated successfully'
        ]);
    } catch (Exception $e) {
        sendResponse(500, [
            'success' => false,
            'message' => 'Error updating user: ' . $e->getMessage()
        ]);
    }
}

function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data);
}