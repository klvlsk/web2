<?php
require_once 'DatabaseRepository.php';
require_once 'Validator.php';
require_once 'template_helpers.php';

session_start();
header('Content-Type: text/html; charset=UTF-8');

$db = new DatabaseRepository();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    handleGetRequest($db);
} else {
    handlePostRequest($db);
}

function handleGetRequest(DatabaseRepository $db) {
    $messages = [];
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', time() - 3600);
        
        if (!empty($_COOKIE['login']) && !empty($_COOKIE['pass'])) {
            $messages[] = [
                'html' => 'Вы можете <a href="login.php">войти</a> с логином <strong>' . 
                          htmlspecialchars($_COOKIE['login']) . 
                          '</strong> и паролем <strong>' . 
                          htmlspecialchars($_COOKIE['pass']) . 
                          '</strong> для изменения данных.'
            ];
        }
    }

    $values = loadUserValues($db);
    $_SESSION['values'] = $values;
    $_SESSION['messages'] = $messages;
    
    include('form.php');
}

function handlePostRequest(DatabaseRepository $db) {
    $values = getFormValues();
    $errors = Validator::validateUserForm($values);
    
    // Убедимся, что $errors - массив
    $_SESSION['errors'] = is_array($errors) ? $errors : [];
    $_SESSION['values'] = $values;
    
    if (!empty($errors)) {
        header('Location: index.php');
        exit();
    }
    
    $isEdit = !empty($_SESSION['login']);
    $userId = $_SESSION['uid'] ?? null;
    
    $result = $isEdit 
        ? $db->updateUser($userId, $values)
        : $db->createUser($values);
    
    setcookie('login', $result['login'], time() + 24 * 60 * 60);
    setcookie('pass', $result['pass'], time() + 24 * 60 * 60);
    setcookie('save', '1', time() + 24 * 60 * 60);
    
    header('Location: index.php');
}

function getFormValues(): array {
    return [
        'full_name' => $_POST['fio'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'email' => $_POST['email'] ?? '',
        'birth_date' => $_POST['birth_date'] ?? '',
        'gender' => $_POST['gender'] ?? '',
        'biography' => $_POST['biography'] ?? '',
        'contract_agreed' => isset($_POST['contract_agreed']),
        'languages' => $_POST['languages'] ?? []
    ];
}

function loadUserValues(DatabaseRepository $db): array {
    $values = [];
    $fields = ['fio', 'phone', 'email', 'birth_date', 'gender', 'biography'];
    
    foreach ($fields as $field) {
        $values[$field] = $_COOKIE[$field.'_value'] ?? '';
        setcookie($field.'_error', '', time() - 3600);
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