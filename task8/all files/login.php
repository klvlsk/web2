<?php
require_once 'DatabaseRepository.php';

session_start();

// Обработка выхода
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Если есть параметры логина и пароля в URL
if (isset($_GET['login']) && isset($_GET['pass'])) {
    $login = $_GET['login'];
    $pass = $_GET['pass'];
    
    // Сохраняем в сессии для отображения в форме
    $_SESSION['login_params'] = [
        'login' => $login,
        'pass' => $pass
    ];
    
    // Перенаправляем без параметров
    header('Location: login.php');
    exit();
}

// Если пользователь уже авторизован
if (!empty($_SESSION['user'])) {
    header('Location: index.php?login=' . urlencode($_SESSION['user']['login']));
    exit();
}

$error_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['login']) || empty($_POST['pass'])) {
        $error_message = 'Логин и пароль обязательны для заполнения';
    } else {
        $db = new DatabaseRepository();
        $user = $db->getUserByLogin($_POST['login']);
        
        if ($user && md5($_POST['pass']) === $user['pass']) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'login' => $user['login'],
                'full_name' => $user['full_name']
            ];
            
            header('Location: index.php?login=' . urlencode($user['login']));
            exit();
        } else {
            $error_message = 'Неверный логин или пароль';
        }
    }
}

// Получаем сохраненные параметры логина/пароля
$login_value = '';
$pass_value = '';
if (isset($_SESSION['login_params'])) {
    $login_value = htmlspecialchars($_SESSION['login_params']['login']);
    $pass_value = htmlspecialchars($_SESSION['login_params']['pass']);
    unset($_SESSION['login_params']);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему</title>
    <link rel="stylesheet" href="loginS.css">
</head>
<body>
    <div class="login-container">
        <h1>Вход в систему</h1>
        <form method="post">
            <input type="text" name="login" placeholder="Логин" required value="<?= $login_value ?>">
            <input type="password" name="pass" placeholder="Пароль" required value="<?= $pass_value ?>">
            <input type="submit" value="Войти">
        </form>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        <a href="index.php" class="back-link">Вернуться на главную</a>
    </div>
</body>
</html>