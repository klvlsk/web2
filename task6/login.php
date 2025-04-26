<?php
require_once 'SessionManager.php';
require_once 'DatabaseRepository.php';

SessionManager::start();

if (SessionManager::isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['login']) || empty($_POST['pass'])) {
        $error = 'Логин и пароль обязательны для заполнения';
    } else {
        $db = new DatabaseRepository();
        $user = $db->checkUserCredentials($_POST['login'], $_POST['pass']);
        
        if ($user) {
            SessionManager::set('login', $_POST['login']);
            SessionManager::set('uid', $user['id']);
            header('Location: index.php');
            exit();
        } else {
            $error = 'Неверный логин или пароль';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h1>Вход в систему</h1>
        <form method="post">
            <input type="text" name="login" placeholder="Логин" required>
            <input type="password" name="pass" placeholder="Пароль" required>
            <input type="submit" value="Войти">
        </form>
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <a href="index.php" class="back-link">Вернуться на главную</a>
    </div>
</body>
</html>