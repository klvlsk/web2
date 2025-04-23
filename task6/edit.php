<?php
require_once 'db.php';

validateAdminCredentials();

$edit_id = $_SESSION['edit_id'] ?? null;
if (!$edit_id) {
    header('Location: admin.php');
    exit();
}

$user_data = getUserData($edit_id);
$user_languages = getUserLanguages($edit_id);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = validateUserData($_POST);
    
    if (empty($errors)) {
        if (updateUserData($edit_id, $_POST)) {
            header('Location: admin.php');
            exit();
        } else {
            $errors[] = 'Ошибка при обновлении данных';
        }
    }
}

$all_languages = getAllLanguages();

function getUserData($id) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM application WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getUserLanguages($id) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getAllLanguages() {
    $db = getDBConnection();
    $stmt = $db->query("SELECT id, language_name FROM programming_languages");
    return $stmt->fetchAll();
}

function updateUserData($id, $data) {
    $db = getDBConnection();
    
    try {
        $db->beginTransaction();
        
        $stmt = $db->prepare("
            UPDATE application SET 
            full_name = ?, phone = ?, email = ?, 
            birth_date = ?, gender = ?, biography = ?, 
            contract_agreed = ? WHERE id = ?
        ");
        $stmt->execute([
            $data['full_name'], $data['phone'], $data['email'],
            $data['birth_date'], $data['gender'], $data['biography'],
            isset($data['contract_agreed']) ? 1 : 0, $id
        ]);

        $stmt = $db->prepare("DELETE FROM application_languages WHERE application_id = ?");
        $stmt->execute([$id]);

        foreach ($data['languages'] as $language_id) {
            $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
            $stmt->execute([$id, $language_id]);
        }

        $db->commit();
        return true;
    } catch (PDOException $e) {
        $db->rollBack();
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование пользователя</title>
    <link rel="stylesheet" href="style3.css">
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