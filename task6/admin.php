<?php
require_once 'AuthMiddleware.php';
require_once 'DatabaseRepository.php';
require_once 'FormRenderer.php';

AuthMiddleware::requireAdmin();

$db = new DatabaseRepository();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        if ($db->deleteUser($_POST['delete'])) {
            SessionManager::setFlash('success', 'Пользователь успешно удален');
        } else {
            SessionManager::setFlash('error', 'Ошибка при удалении пользователя');
        }
        header('Location: admin.php');
        exit();
    } elseif (isset($_POST['edit'])) {
        SessionManager::set('edit_id', $_POST['edit']);
        header('Location: edit.php');
        exit();
    }
}

$applications = $db->getAllApplications();
$language_stats = $db->getLanguageStatistics();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>
    <div class="admin-container">
        <h1>Админ-панель</h1>
        
        <?php $flash = SessionManager::getFlash(); ?>
        <?php if ($flash): ?>
            <div class="flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>
        
        <h2>Все заявки</h2>
        <?= FormRenderer::renderTable($applications, [
            'id' => 'ID',
            'full_name' => 'ФИО',
            'phone' => 'Телефон',
            'email' => 'Email',
            'birth_date' => 'Дата рождения',
            'gender' => ['title' => 'Пол', 'formatter' => fn($v) => $v == 'male' ? 'Мужской' : 'Женский'],
            'languages_list' => 'Языки',
            'biography' => ['title' => 'Биография', 'formatter' => fn($v) => substr($v, 0, 50).(strlen($v) > 50 ? '...' : '')],
            'contract_agreed' => ['title' => 'Контракт', 'formatter' => fn($v) => $v ? 'Да' : 'Нет']
        ], [
            'edit' => 'Редактировать',
            'delete' => 'Удалить'
        ]) ?>
        
        <h2>Статистика по языкам</h2>
        <?= FormRenderer::renderTable($language_stats, [
            'language_name' => 'Язык',
            'user_count' => 'Количество пользователей'
        ]) ?>
    </div>
    <script>
        document.querySelectorAll('button[name="delete"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (!confirm('Вы уверены, что хотите удалить этого пользователя?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>