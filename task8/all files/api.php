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
    if (!isset($_GET['action'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action parameter is required']);
        return;
    }

    switch ($_GET['action']) {
        case 'get_user':
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
            echo json_encode(['success' => true, 'data' => $user]);
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
            break;
    }
}

function handlePostRequest(DatabaseRepository $db) {
    // Получаем данные из JSON или формы
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        $data = $_POST;
        // Обрабатываем множественный select
        $data['languages'] = isset($_POST['languages']) ? (is_array($_POST['languages']) ? $_POST['languages'] : [$_POST['languages']]) : [];
        $data['contract_agreed'] = isset($_POST['contract_agreed']);
    }

    // Валидация
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
        // Создание пользователя
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

function handlePutRequest(DatabaseRepository $db) {
    // Проверка авторизации
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
    
    // Получаем данные из JSON
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    if (strpos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'JSON data required']);
        return;
    }
    
    // Валидация
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
        // Обновление пользователя
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