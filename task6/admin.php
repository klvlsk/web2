<?php
require_once 'db.php';

validateAdminCredentials();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    handleAdminAction();
}

$applications = getAllApplications();
$language_stats = getLanguageStatistics();

function handleAdminAction() {
    $db = getDBConnection();
    
    if (isset($_POST['delete'])) {
        $stmt = $db->prepare("DELETE FROM application WHERE id = ?");
        $stmt->execute([$_POST['delete']]);
    } elseif (isset($_POST['edit'])) {
        $_SESSION['edit_id'] = $_POST['edit'];
        header('Location: edit.php');
        exit();
    }
}

function getAllApplications() {
    $db = getDBConnection();
    $stmt = $db->prepare("
        SELECT a.*, GROUP_CONCAT(p.language_name SEPARATOR ', ') as languages_list 
        FROM application a
        LEFT JOIN application_languages al ON a.id = al.application_id
        LEFT JOIN programming_languages p ON al.language_id = p.id
        GROUP BY a.id
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getLanguageStatistics() {
    $db = getDBConnection();
    $stmt = $db->prepare("
        SELECT p.id, p.language_name, COUNT(al.application_id) as user_count 
        FROM programming_languages p
        LEFT JOIN application_languages al ON p.id = al.language_id
        GROUP BY p.id
        ORDER BY user_count DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}
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
        
        <h2>Все заявки</h2>
        <?= renderApplicationsTable($applications) ?>
        
        <h2>Статистика по языкам</h2>
        <?= renderLanguageStats($language_stats) ?>
    </div>
</body>
</html>

<?php
function renderApplicationsTable($applications) {
    $html = '<table><thead><tr>
        <th>ID</th><th>ФИО</th><th>Телефон</th><th>Email</th>
        <th>Дата рождения</th><th>Пол</th><th>Языки</th>
        <th>Биография</th><th>Контракт</th><th>Действия</th>
    </tr></thead><tbody>';

    foreach ($applications as $app) {
        $html .= '<tr>
            <td>'.htmlspecialchars($app['id']).'</td>
            <td>'.htmlspecialchars($app['full_name']).'</td>
            <td>'.htmlspecialchars($app['phone']).'</td>
            <td>'.htmlspecialchars($app['email']).'</td>
            <td>'.htmlspecialchars($app['birth_date']).'</td>
            <td>'.($app['gender'] == 'male' ? 'Мужской' : 'Женский').'</td>
            <td>'.htmlspecialchars($app['languages_list'] ?? 'Не указано').'</td>
            <td>'.htmlspecialchars(truncateText($app['biography'], 50)).'</td>
            <td>'.($app['contract_agreed'] ? 'Да' : 'Нет').'</td>
            <td>
                <form method="POST" style="display:inline;">
                    <button type="submit" name="edit" value="'.$app['id'].'">Редактировать</button>
                </form>
                <form method="POST" style="display:inline;">
                    <button type="submit" name="delete" value="'.$app['id'].'">Удалить</button>
                </form>
            </td>
        </tr>';
    }

    return $html.'</tbody></table>';
}

function renderLanguageStats($stats) {
    $html = '<table><thead><tr><th>Язык</th><th>Количество пользователей</th></tr></thead><tbody>';
    
    foreach ($stats as $stat) {
        $html .= '<tr>
            <td>'.htmlspecialchars($stat['language_name']).'</td>
            <td>'.htmlspecialchars($stat['user_count']).'</td>
        </tr>';
    }
    
    return $html.'</tbody></table>';
}

function truncateText($text, $length) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length).'...';
    }
    return $text;
}