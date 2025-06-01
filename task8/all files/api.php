<?php
require_once 'DatabaseRepository.php';
require_once 'Validator.php';

session_start();

$db = new DatabaseRepository();
$method = $_SERVER['REQUEST_METHOD'];
$contentType = $_SERVER['CONTENT_TYPE'] ?? 'application/json';

// Определяем тип ответа
$acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? 'application/json';
$responseType = strpos($acceptHeader, 'application/xml') !== false ? 'xml' : 'json';

// Парсим входные данные
$input = file_get_contents('php://input');
$data = [];

if ($method === 'POST' || $method === 'PUT') {
    if (strpos($contentType, 'application/xml') !== false) {
    try {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($input);
        if ($xml === false) {
            throw new Exception('Invalid XML: ' . libxml_get_last_error()->message);
        }
        $data = json_decode(json_encode($xml), true);
        // Добавьте обработку языков
        if (isset($data['languages']) && is_array($data['languages']['language'])) {
            $data['languages'] = $data['languages']['language'];
        } elseif (isset($data['languages']['language'])) {
            $data['languages'] = [$data['languages']['language']];
        }
    } catch (Exception $e) {
        sendResponse(400, [
            'success' => false,
            'message' => $e->getMessage()
        ], $responseType);
        exit;
    }
}
}

// Обработка запросов
switch ($method) {
    case 'GET':
        handleGetRequest($db, $_GET, $responseType);
        break;
    case 'POST':
        handlePostRequest($db, $data, $responseType);
        break;
    case 'PUT':
        handlePutRequest($db, $data, $responseType);
        break;
    default:
        sendResponse(405, [
            'success' => false,
            'message' => 'Method not allowed'
        ], $responseType);
        break;
}

function handleGetRequest(DatabaseRepository $db, $params, $responseType) {
    if (empty($params['login'])) {
        sendResponse(400, [
            'success' => false,
            'message' => 'Login parameter is required'
        ], $responseType);
        return;
    }

    $user = $db->getUserByLogin($params['login']);
    if (!$user) {
        sendResponse(404, [
            'success' => false,
            'message' => 'User not found'
        ], $responseType);
        return;
    }

    $user['languages'] = $db->getUserLanguages($user['id']);
    unset($user['pass']);
    
    sendResponse(200, [
        'success' => true,
        'data' => $user
    ], $responseType);
}

function handlePostRequest(DatabaseRepository $db, $data, $responseType) {
    $errors = Validator::validateUserForm($data);
    if (!empty($errors)) {
        sendResponse(400, [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ], $responseType);
        return;
    }
    
    try {
        $result = $db->createUser($data);
        
        // Изменяем URL для перехода на страницу логина
        sendResponse(200, [
            'success' => true,
            'message' => 'User created successfully',
            'login' => $result['login'],
            'password' => $result['pass'],
            'profile_url' => 'login.php?login=' . urlencode($result['login']) . '&pass=' . urlencode($result['pass'])
        ], $responseType);
    } catch (Exception $e) {
        sendResponse(500, [
            'success' => false,
            'message' => 'Error creating user: ' . $e->getMessage()
        ], $responseType);
    }
}

function handlePutRequest(DatabaseRepository $db, $data, $responseType) {
    if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
        sendResponse(401, [
            'success' => false,
            'message' => 'Authentication required'
        ], $responseType);
        return;
    }
    
    $user = $db->checkUserCredentials($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    if (!$user) {
        sendResponse(403, [
            'success' => false,
            'message' => 'Invalid credentials'
        ], $responseType);
        return;
    }
    
    $errors = Validator::validateUserForm($data);
    if (!empty($errors)) {
        sendResponse(400, [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ], $responseType);
        return;
    }
    
    try {
        $db->updateUser($user['id'], $data);
        
        sendResponse(200, [
            'success' => true,
            'message' => 'User updated successfully'
        ], $responseType);
    } catch (Exception $e) {
        sendResponse(500, [
            'success' => false,
            'message' => 'Error updating user: ' . $e->getMessage()
        ], $responseType);
    }
}

function sendResponse($statusCode, $data, $type = 'json') {
    http_response_code($statusCode);
    
    if ($type === 'xml') {
        header('Content-Type: application/xml');
        echo arrayToXml($data);
    } else {
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}

function arrayToXml($data, $rootNode = 'response') {
    $xml = '<?xml version="1.0"?>';
    $xml .= "<$rootNode>";
    
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            if ($key === 'errors') {
                $xml .= "<$key>";
                foreach ($value as $errorKey => $errorValue) {
                    $xml .= "<$errorKey>" . htmlspecialchars($errorValue) . "</$errorKey>";
                }
                $xml .= "</$key>";
            } elseif ($key === 'data') {
                $xml .= "<$key>";
                foreach ($value as $dataKey => $dataValue) {
                    if ($dataKey === 'languages' && is_array($dataValue)) {
                        $xml .= "<$dataKey>";
                        foreach ($dataValue as $lang) {
                            $xml .= "<language>" . htmlspecialchars($lang) . "</language>";
                        }
                        $xml .= "</$dataKey>";
                    } else {
                        $xml .= "<$dataKey>" . htmlspecialchars($dataValue) . "</$dataKey>";
                    }
                }
                $xml .= "</$data>";
            } else {
                $xml .= arrayToXml($value, $key);
            }
        } else {
            $xml .= "<$key>" . htmlspecialchars($value) . "</$key>";
        }
    }
    
    $xml .= "</$rootNode>";
    return $xml;
}