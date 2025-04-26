<?php
require_once 'SessionManager.php';
require_once 'DatabaseRepository.php';
require_once 'Validator.php';
require_once 'FormRenderer.php';

SessionManager::start();
header('Content-Type: text/html; charset=UTF-8');

$db = new DatabaseRepository();

if (isset($_GET['logout'])) {
    SessionManager::logout();
    SessionManager::setFlash('info', 'Готово к созданию нового пользователя');
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
    
    if (empty($errors)) {
        if (SessionManager::isLoggedIn()) {
            $db->updateUser(SessionManager::get('uid'), $values);
            SessionManager::setFlash('success', 'Данные успешно обновлены');
        } else {
            $result = $db->createUser($values);
            setcookie('login', $result['login'], time() + 365 * 24 * 60 * 60);
            setcookie('pass', $result['pass'], time() + 365 * 24 * 60 * 60);
            SessionManager::setFlash('success', 
                'Новый пользователь создан. Вы можете <a href="login.php">войти</a> с логином <strong>'.
                htmlspecialchars($result['login']).'</strong> и паролем <strong>'.
                htmlspecialchars($result['pass']).'</strong> для изменения данных.');
        }
        header('Location: index.php');
        exit();
    }
    
    SessionManager::set('errors', $errors);
    SessionManager::set('values', $values);
    header('Location: index.php');
    exit();
}

// Отображение формы
$values = SessionManager::get('values', []);
$errors = SessionManager::get('errors', []);
$messages = SessionManager::getFlash();

if (SessionManager::isLoggedIn()) {
    $user_data = $db->getUser(SessionManager::get('uid'));
    if ($user_data) {
        $values = [
            'fio' => $user_data['full_name'],
            'phone' => $user_data['phone'],
            'email' => $user_data['email'],
            'birth_date' => $user_data['birth_date'],
            'gender' => $user_data['gender'],
            'biography' => $user_data['biography'],
            'contract_agreed' => $user_data['contract_agreed'],
            'languages' => $db->getUserLanguages(SessionManager::get('uid'))
        ];
    }
}

include('form.php');