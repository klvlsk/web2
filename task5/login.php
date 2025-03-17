<?php
header('Content-Type: text/html; charset=UTF-8');

// Начинаем сессию, если она ещё не начата
if (!session_id()) {
    session_start();
}

// Если пользователь уже авторизован, перенаправляем его на index.php
if (!empty($_SESSION['login'])) {
    header('Location: index.php');
    exit();
}

$error_message = ''; // Переменная для хранения сообщения об ошибке

// Обработка POST-запроса (попытка входа)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = 'u68596';
    $pass = '2859691';
    $db = new PDO('mysql:host=localhost;dbname=u68596', $user, $pass, [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Проверяем логин и пароль
    $stmt = $db->prepare("SELECT id, pass FROM application WHERE login = ?");
    $stmt->execute([$_POST['login']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_data || md5($_POST['pass']) !== $user_data['pass']) {
        $error_message = 'Неверный логин или пароль.'; // Сообщение об ошибке
    } else {
        // Если данные верны, сохраняем логин и ID пользователя в сессии
        $_SESSION['login'] = $_POST['login'];
        $_SESSION['uid'] = $user_data['id'];
        header('Location: index.php'); // Перенаправляем на главную страницу
        exit();
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
        <form action="login.php" method="post">
            <input type="text" name="login" placeholder="Логин" required>
            <input type="password" name="pass" placeholder="Пароль" required>
            <input type="submit" value="Войти">
        </form>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <a href="index.php" class="back-link">Вернуться на главную</a>
    </div>
</body>
</html>