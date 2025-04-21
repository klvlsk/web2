<?php
$user = 'u68596';
$pass = '2859691';
$db = new PDO('mysql:host=localhost;dbname=u68596', $user, $pass, [
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// Создание таблицы администраторов, если её нет
$db->exec("
    CREATE TABLE IF NOT EXISTS admin_users (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        login VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(32) NOT NULL,
        PRIMARY KEY (id)
    )
");

// Проверка наличия администратора
$stmt = $db->query("SELECT COUNT(*) FROM admin_users");
if ($stmt->fetchColumn() == 0) {
    $default_admin = 'admin';
    $default_pass = '123';
    $stmt = $db->prepare("INSERT INTO admin_users (login, password_hash) VALUES (?, ?)");
    $stmt->execute([$default_admin, md5($default_pass)]);
}