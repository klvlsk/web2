<?php
require_once 'db.php';
require_once 'form_helpers.php';

session_start();
header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    handleGetRequest();
} else {
    handlePostRequest();
}

function handleGetRequest() {
    $messages = [];
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', time() - 3600);
        
        // Показываем сообщение только если есть и логин, и пароль в куках
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

    $values = loadUserValues();
    $_SESSION['values'] = $values;
    $_SESSION['messages'] = $messages;
    
    include('form.php');
}

function handlePostRequest() {
    $values = getFormValues();
    $errors = validateForm($values);
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['values'] = $values;
        header('Location: index.php');
        exit();
    }
    
    $isEdit = !empty($_SESSION['login']);
    $userId = $_SESSION['uid'] ?? null;
    
    $result = saveUserData($values, $isEdit, $userId);
    
    // Всегда устанавливаем куки с логином и паролем
    setcookie('login', $result['login'], time() + 24 * 60 * 60);
    setcookie('pass', $result['pass'], time() + 24 * 60 * 60);
    setcookie('save', '1', time() + 24 * 60 * 60);
    
    header('Location: index.php');
}

function loadUserValues() {
    $values = [];
    $fields = ['fio', 'phone', 'email', 'birth_date', 'gender', 'biography'];
    
    foreach ($fields as $field) {
        $values[$field] = isset($_COOKIE[$field.'_value']) 
            ? trim($_COOKIE[$field.'_value']) 
            : '';
        setcookie($field.'_error', '', time() - 3600);
    }
    
    $values['languages'] = !empty($_COOKIE['languages_value']) ? 
        json_decode($_COOKIE['languages_value'], true) : [];
    $values['contract_agreed'] = !empty($_COOKIE['contract_agreed_value']);

    if (!empty($_SESSION['login'])) {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM application WHERE login = ?");
        $stmt->execute([$_SESSION['login']]);
        $user_data = $stmt->fetch();

        if ($user_data) {
            $values = [
                'fio' => $user_data['full_name'],
                'phone' => $user_data['phone'],
                'email' => $user_data['email'],
                'birth_date' => $user_data['birth_date'],
                'gender' => $user_data['gender'],
                'biography' => trim($user_data['biography']),
                'contract_agreed' => $user_data['contract_agreed']
            ];

            $stmt = $db->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
            $stmt->execute([$_SESSION['uid']]);
            $values['languages'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
    }
    
    return $values;
}