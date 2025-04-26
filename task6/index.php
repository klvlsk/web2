<?php
require_once 'DatabaseRepository.php';
require_once 'Validator.php';
require_once 'template_helpers.php';

session_start();

// В начале файла, после session_start()
if (isset($_GET['logout'])) {
    // Полностью очищаем сессию, но сохраняем куки
    $_SESSION = [];
    session_destroy();
    session_start(); // Начинаем новую сессию
    
    // Перенаправляем с флагом new_user
    header('Location: index.php?new_user=1');
    exit();
}

header('Content-Type: text/html; charset=UTF-8');

$db = new DatabaseRepository();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    handleGetRequest($db);
} else {
    handlePostRequest($db);
}

function handleGetRequest(DatabaseRepository $db) {
    $messages = [];
    
    // Показываем специальное сообщение после выхода
    if (isset($_GET['new_user'])) {
        $messages[] = ['html' => 'Готово к созданию нового пользователя'];
    }
    // Старое сообщение с учетными данными
    elseif (!empty($_COOKIE['save'])) {
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

// В handlePostRequest function:
function handlePostRequest(DatabaseRepository $db) {
    $values = getFormValues();
    $errors = Validator::validateUserForm($values);
    
    saveValidFieldsToCookies($values, $errors);
    
    $_SESSION['errors'] = is_array($errors) ? $errors : [];
    $_SESSION['values'] = $values;
    
    if (!empty($errors)) {
        header('Location: index.php');
        exit();
    }
    
    // Всегда создаем нового пользователя, если не в режиме редактирования
    if (empty($_SESSION['login'])) {
        $result = $db->createUser($values);
        $_SESSION['messages'] = [[
            'html' => 'Вы можете <a href="login.php">войти</a> с логином <strong>' . 
                     htmlspecialchars($result['login']) . 
                     '</strong> и паролем <strong>' . 
                     htmlspecialchars($result['pass']) . 
                     '</strong> для изменения данных.'
        ]];
        
        setcookie('login', $result['login'], time() + 365 * 24 * 60 * 60);
        setcookie('pass', $result['pass'], time() + 365 * 24 * 60 * 60);
    } else {
        // Режим редактирования существующего пользователя
        $db->updateUser($_SESSION['uid'], $values);
        $_SESSION['messages'] = [['html' => 'Данные успешно обновлены']];
    }
    
    setcookie('save', '1', time() + 365 * 24 * 60 * 60);
    header('Location: index.php');
    exit();
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
    
    foreach ($validFields as $field => $value) {
        // Сохраняем только те поля, которые прошли валидацию
        if (!isset($errors[$field])) {
            if ($field === 'languages') {
                setcookie('languages_value', json_encode($value), time() + 365 * 24 * 60 * 60);
            } elseif ($field === 'contract_agreed') {
                setcookie('contract_agreed_value', $value ? '1' : '', time() + 365 * 24 * 60 * 60);
            } else {
                setcookie($field.'_value', $value, time() + 365 * 24 * 60 * 60);
            }
        }
    }
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