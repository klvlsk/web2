<?php
require_once 'db.php';

session_start();
header('Content-Type: text/html; charset=UTF-8');

if (!empty($_SESSION['login'])) {
    header('Location: index.php');
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $error_message = handleLogin($_POST['login'], $_POST['pass']);
}

function handleLogin($login, $password) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id, pass FROM application WHERE login = ?");
    $stmt->execute([$login]);
    $user_data = $stmt->fetch();

    if (!$user_data || md5($password) !== $user_data['pass']) {
        return 'Неверный логин или пароль.';
    }

    $_SESSION['login'] = $login;
    $_SESSION['uid'] = $user_data['id'];
    header('Location: index.php');
    exit();
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
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        <a href="index.php" class="back-link">Вернуться на главную</a>
    </div>
</body>
</html>