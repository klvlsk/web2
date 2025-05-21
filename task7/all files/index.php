<?php
require_once 'DatabaseRepository.php';
require_once 'Validator.php';
require_once 'template_helpers.php';

session_start();
header('Content-Type: text/html; charset=UTF-8');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$db = new DatabaseRepository();

if (isset($_GET['logout'])) {
    unset($_SESSION['login'], $_SESSION['uid']);
    $_SESSION['new_session'] = true;
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    handleGetRequest($db);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handlePostRequest($db);
}

function handleGetRequest(DatabaseRepository $db) {
    $messages = [];
    
    if (!empty($_SESSION['new_session'])) {
        $messages[] = ['html' => 'Готово к созданию нового пользователя'];
        unset($_SESSION['new_session']);
    }
    elseif (!empty($_COOKIE['save']) && !empty($_COOKIE['login']) && !empty($_COOKIE['pass'])) {
        setcookie('save', '', time() - 3600);
        $messages[] = [
            'html' => 'Вы можете <a href="login.php">войти</a> с логином <strong>' . 
                  htmlspecialchars($_COOKIE['login']) . 
                  '</strong> и паролем <strong>' . 
                  htmlspecialchars($_COOKIE['pass']) . 
                  '</strong> для изменения данных.',
            'raw_html' => true
        ];
    }

    $values = loadUserValues($db);
    $_SESSION['values'] = $values;
    $_SESSION['messages'] = $messages;
    
    include('form.php');
    exit();
}

function handlePostRequest(DatabaseRepository $db) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Неверный CSRF-токен');
    }

    $values = [
        'full_name' => $_POST['fio'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'email' => $_POST['email'] ?? '',
        'birth_date' => $_POST['birth_date'] ?? '',
        'gender' => $_POST['gender'] ?? '',
        'biography' => $_POST['biography'] ?? '',
        'contract_agreed' => isset($_POST['contract_agreed']),
        'languages' => $_POST['languages'] ?? []
    ];

    $errors = Validator::validateUserForm($values);
    
    saveValidFieldsToCookies($values, $errors);
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['values'] = $values;
        header('Location: index.php');
        exit();
    }

    try {
        if (empty($_SESSION['login'])) {
            $result = $db->createUser($values);
            
            $config = require __DIR__ . '/../plus/config.php';
            $cookieOptions = $config['security']['cookie_options'];
            $cookieOptions['expires'] = time() + $config['security']['cookie_options']['lifetime'];
            
            setcookie('login', $result['login'], $cookieOptions);
            setcookie('pass', $result['pass'], $cookieOptions);
            setcookie('save', '1', $cookieOptions);
            
            $_SESSION['messages'] = [[
                'html' => 'Новый пользователь создан. Вы можете <a href="login.php">войти</a> с логином <strong>' . 
                         htmlspecialchars($result['login']) . 
                         '</strong> и паролем <strong>' . 
                         htmlspecialchars($result['pass']) . 
                         '</strong> для изменения данных.',
                'raw_html' => true
            ]];
        } else {
            $db->updateUser($_SESSION['uid'], $values);
            $_SESSION['messages'] = [['html' => 'Данные успешно обновлены']];
        }
        
        header('Location: index.php');
        exit();
    } catch (Exception $e) {
        error_log("Error saving user data: " . $e->getMessage());
        $_SESSION['errors']['general'] = 'Ошибка при сохранении данных';
        header('Location: index.php');
        exit();
    }
}

function saveValidFieldsToCookies(array $values, array $errors): void {
    $validFields = [
        'fio' => $values['full_name'],
        'phone' => $values['phone'],
        'email' => $values['email'],
        'birth_date' => $values['birth_date'],
        'gender' => $values['gender'],
        'biography' => $values['biography'],
        'languages' => $values['languages'],
        'contract_agreed' => $values['contract_agreed']
    ];
    
    $config = require __DIR__ . '/../plus/config.php';
    $cookieOptions = $config['security']['cookie_options'];
    $cookieOptions['expires'] = time() + $config['security']['cookie_options']['lifetime'];
    
    foreach ($validFields as $field => $value) {
        if (!isset($errors[$field])) {
            if ($field === 'languages') {
                setcookie('languages_value', json_encode($value), $cookieOptions);
            } elseif ($field === 'contract_agreed') {
                setcookie('contract_agreed_value', $value ? '1' : '', $cookieOptions);
            } else {
                setcookie($field.'_value', $value, $cookieOptions);
            }
        }
    }
}

function loadUserValues(DatabaseRepository $db): array {
    $values = [];
    $fields = ['fio', 'phone', 'email', 'birth_date', 'gender', 'biography'];
    
    foreach ($fields as $field) {
        $values[$field] = $_COOKIE[$field.'_value'] ?? '';
    }
    
    $values['languages'] = !empty($_COOKIE['languages_value']) ? 
        json_decode($_COOKIE['languages_value'], true) : [];
    $values['contract_agreed'] = !empty($_COOKIE['contract_agreed_value']);

    if (!empty($_SESSION['login'])) {
        $user_data = $db->getUser($_SESSION['uid']);
        
        if ($user_data) {
            $values = [
                'fio' => $user_data['full_name'],
                'phone' => $user_data['phone'],
                'email' => $user_data['email'],
                'birth_date' => $user_data['birth_date'],
                'gender' => $user_data['gender'],
                'biography' => $user_data['biography'],
                'contract_agreed' => $user_data['contract_agreed'],
                'languages' => $db->getUserLanguages($_SESSION['uid'])
            ];
        }
    }
    
    return $values;
}