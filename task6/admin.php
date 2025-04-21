<?php
session_start();
require_once 'db.php';

// Проверка HTTP-авторизации
if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    echo '<h1>401 Требуется авторизация</h1>';
    exit();
}

// Проверка учетных данных администратора
$stmt = $db->prepare("SELECT * FROM admin_users WHERE login = ?");
$stmt->execute([$_SERVER['PHP_AUTH_USER']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin || md5($_SERVER['PHP_AUTH_PW']) !== $admin['password_hash']) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    echo '<h1>401 Неверные учетные данные</h1>';
    exit();
}

// Обработка действий администратора
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        $stmt = $db->prepare("DELETE FROM application WHERE id = ?");
        $stmt->execute([$_POST['delete']]);
    } elseif (isset($_POST['edit'])) {
        $_SESSION['edit_id'] = $_POST['edit'];
        header('Location: edit.php');
        exit();
    }
}

// Получение всех данных пользователей
$stmt = $db->prepare("
    SELECT a.*, GROUP_CONCAT(p.language_name SEPARATOR ', ') as languages_list 
    FROM application a
    LEFT JOIN application_languages al ON a.id = al.application_id
    LEFT JOIN programming_languages p ON al.language_id = p.id
    GROUP BY a.id
");
$stmt->execute();
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получение статистики по языкам
$stmt = $db->prepare("
    SELECT p.id, p.language_name, COUNT(al.application_id) as user_count 
    FROM programming_languages p
    LEFT JOIN application_languages al ON p.id = al.language_id
    GROUP BY p.id
    ORDER BY user_count DESC
");
$stmt->execute();
$language_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="admin-container">
        <h1>Админ-панель</h1>
        
        <h2>Все заявки</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ФИО</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Дата рождения</th>
                    <th>Пол</th>
                    <th>Языки</th>
                    <th>Биография</th>
                    <th>Контракт</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                <tr>
                    <td><?= htmlspecialchars($app['id']) ?></td>
                    <td><?= htmlspecialchars($app['full_name']) ?></td>
                    <td><?= htmlspecialchars($app['phone']) ?></td>
                    <td><?= htmlspecialchars($app['email']) ?></td>
                    <td><?= htmlspecialchars($app['birth_date']) ?></td>
                    <td><?= $app['gender'] == 'male' ? 'Мужской' : 'Женский' ?></td>
                    <td><?= htmlspecialchars($app['languages_list'] ?? 'Не указано') ?></td>
                    <td><?= htmlspecialchars(substr($app['biography'], 0, 50)) ?>...</td>
                    <td><?= $app['contract_agreed'] ? 'Да' : 'Нет' ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <button type="submit" name="edit" value="<?= $app['id'] ?>">Редактировать</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <button type="submit" name="delete" value="<?= $app['id'] ?>">Удалить</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h2>Статистика по языкам</h2>
        <table>
            <thead>
                <tr>
                    <th>Язык</th>
                    <th>Количество пользователей</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($language_stats as $stat): ?>
                <tr>
                    <td><?= htmlspecialchars($stat['language_name']) ?></td>
                    <td><?= htmlspecialchars($stat['user_count']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>