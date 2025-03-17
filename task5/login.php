<?php
header('Content-Type: text/html; charset=UTF-8');

$session_started = false;
if ($_COOKIE[session_name()] && session_start()) {
    $session_started = true;
    if (!empty($_SESSION['login'])) {
        header('Location: index.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    ?>
    <form action="" method="post">
        <input name="login" placeholder="Логин" />
        <input name="pass" type="password" placeholder="Пароль" />
        <input type="submit" value="Войти" />
    </form>
    <?php
} else {
    $user = 'u68596';
    $pass = '2859691';
    $db = new PDO('mysql:host=localhost;dbname=u68596', $user, $pass, [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $db->prepare("SELECT id, pass FROM application WHERE login = ?");
    $stmt->execute([$_POST['login']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_data || md5($_POST['pass']) !== $user_data['pass']) {
        print('Неверный логин или пароль.');
        exit();
    }

    if (!$session_started) {
        session_start();
    }
    $_SESSION['login'] = $_POST['login'];
    $_SESSION['uid'] = $user_data['id'];

    header('Location: index.php');
}
?>