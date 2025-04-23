<?php
function getDBConnection() {
    static $db = null;
    if ($db === null) {
        $user = 'u68596';
        $pass = '2859691';
        $db = new PDO('mysql:host=localhost;dbname=u68596', $user, $pass, [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        initDatabase($db);
    }
    return $db;
}

function initDatabase($db) {
    $stmt = $db->query("SELECT COUNT(*) FROM admin_users");
    if ($stmt->fetchColumn() == 0) {
        $stmt = $db->prepare("INSERT INTO admin_users (login, password_hash) VALUES (?, ?)");
        $stmt->execute(['admin', md5('123')]);
    }
}

function validateAdminCredentials() {
    if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        echo '<h1>401 Требуется авторизация</h1>';
        exit();
    }

    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE login = ?");
    $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
    $admin = $stmt->fetch();

    if (!$admin || md5($_SERVER['PHP_AUTH_PW']) !== $admin['password_hash']) {
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        echo '<h1>401 Неверные учетные данные</h1>';
        exit();
    }
}