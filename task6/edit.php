<?php
session_start();
require_once 'db.php';

// Проверка авторизации администратора
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

// Получение данных пользователя для редактирования
$edit_id = $_SESSION['edit_id'] ?? null;
if (!$edit_id) {
    header('Location: admin.php');
    exit();
}

$stmt = $db->prepare("SELECT * FROM application WHERE id = ?");
$stmt->execute([$edit_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    header('Location: admin.php');
    exit();
}

$stmt = $db->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
$stmt->execute([$edit_id]);
$user_languages = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    
    // Валидация данных
    if (empty($_POST['full_name']) || !preg_match('/^[A-Za-zА-Яа-я\s]{1,150}$/u', $_POST['full_name'])) {
        $errors[] = 'Неверное ФИО';
    }
    if (empty($_POST['phone']) || !preg_match('/^\+7\d{10}$/', $_POST['phone'])) {
        $errors[] = 'Неверный телефон';
    }
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Неверный email';
    }
    if (empty($_POST['birth_date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['birth_date'])) {
        $errors[] = 'Неверная дата рождения';
    }
    if (empty($_POST['gender']) || !in_array($_POST['gender'], ['male', 'female'])) {
        $errors[] = 'Не указан пол';
    }
    if (empty($_POST['languages']) || !is_array($_POST['languages'])) {
        $errors[] = 'Не выбраны языки программирования';
    }
    if (empty(trim($_POST['biography'])) || strlen($_POST['biography']) > 500) {
        $errors[] = 'Неверная биография';
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            // Обновление основной информации
            $stmt = $db->prepare("
                UPDATE application SET 
                full_name = ?, 
                phone = ?, 
                email = ?, 
                birth_date = ?, 
                gender = ?, 
                biography = ?, 
                contract_agreed = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['full_name'],
                $_POST['phone'],
                $_POST['email'],
                $_POST['birth_date'],
                $_POST['gender'],
                $_POST['biography'],
                isset($_POST['contract_agreed']) ? 1 : 0,
                $edit_id
            ]);

            // Удаление старых языков
            $stmt = $db->prepare("DELETE FROM application_languages WHERE application_id = ?");
            $stmt->execute([$edit_id]);

            // Добавление новых языков
            foreach ($_POST['languages'] as $language_id) {
                $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
                $stmt->execute([$edit_id, $language_id]);
            }

            $db->commit();
            header('Location: admin.php');
            exit();
        } catch (PDOException $e) {
            $db->rollBack();
            $errors[] = 'Ошибка при обновлении данных: ' . $e->getMessage();
        }
    }
}

// Получение списка всех языков
$stmt = $db->query("SELECT id, language_name FROM programming_languages");
$all_languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование пользователя</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="edit-container">
        <h2>Редактирование пользователя #<?= htmlspecialchars($edit_id) ?></h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <label>ФИО:</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($user_data['full_name']) ?>" required>
            
            <label>Телефон:</label>
            <input type="tel" name="phone" value="<?= htmlspecialchars($user_data['phone']) ?>" required>
            
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required>
            
            <label>Дата рождения:</label>
            <input type="date" name="birth_date" value="<?= htmlspecialchars($user_data['birth_date']) ?>" required>
            
            <label>Пол:</label>
            <input type="radio" name="gender" value="male" <?= $user_data['gender'] == 'male' ? 'checked' : '' ?> required> Мужской
            <input type="radio" name="gender" value="female" <?= $user_data['gender'] == 'female' ? 'checked' : '' ?>> Женский
            
            <label>Любимый язык программирования:</label>
            <select name="languages[]" multiple required style="height: 150px;">
                <?php foreach ($all_languages as $lang): ?>
                    <option value="<?= $lang['id'] ?>" <?= in_array($lang['id'], $user_languages) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lang['language_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label>Биография:</label>
            <textarea name="biography" required><?= htmlspecialchars($user_data['biography']) ?></textarea>
            
            <label>
                <input type="checkbox" name="contract_agreed" <?= $user_data['contract_agreed'] ? 'checked' : '' ?>>
                Согласен с контрактом
            </label>
            
            <button type="submit">Сохранить изменения</button>
            <a href="admin.php" class="cancel-button">Отмена</a>
        </form>
    </div>
</body>
</html>