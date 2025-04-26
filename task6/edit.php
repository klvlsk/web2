<?php
require_once 'AuthMiddleware.php';
require_once 'DatabaseRepository.php';
require_once 'FormRenderer.php';
require_once 'Validator.php';

AuthMiddleware::requireAdmin();

$db = new DatabaseRepository();
$edit_id = SessionManager::get('edit_id');

if (!$edit_id) {
    header('Location: admin.php');
    exit();
}

$user_data = $db->getUser($edit_id);
$user_languages = $db->getUserLanguages($edit_id);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = Validator::validateUserForm($_POST);
    
    if (empty($errors)) {
        $db->updateUser($edit_id, $_POST);
        SessionManager::setFlash('success', 'Данные пользователя обновлены');
        header('Location: admin.php');
        exit();
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
            <?= FormRenderer::renderField('text', 'full_name', 'ФИО', $errors, $user_data, ['required' => '']) ?>
            <?= FormRenderer::renderField('tel', 'phone', 'Телефон', $errors, $user_data, ['required' => '']) ?>
            <?= FormRenderer::renderField('email', 'email', 'Email', $errors, $user_data, ['required' => '']) ?>
            <?= FormRenderer::renderField('date', 'birth_date', 'Дата рождения', $errors, $user_data, ['required' => '']) ?>
            
            <label>Пол:</label>
            <?= FormRenderer::renderRadio('gender', 'male', 'Мужской', $user_data) ?>
            <?= FormRenderer::renderRadio('gender', 'female', 'Женский', $user_data) ?>
            <?= !empty($errors['gender']) ? '<div class="error-message">'.htmlspecialchars($errors['gender']).'</div>' : '' ?>
            
            <label>Любимый язык программирования:</label>
            <?= FormRenderer::renderSelectLanguages($user_languages, $db) ?>
            <?= !empty($errors['languages']) ? '<div class="error-message">'.htmlspecialchars($errors['languages']).'</div>' : '' ?>
            
            <?= FormRenderer::renderField('textarea', 'biography', 'Биография', $errors, $user_data, ['required' => '', 'maxlength' => '500']) ?>
            
            <label>
                <input type="checkbox" name="contract_agreed" <?= $user_data['contract_agreed'] ? 'checked' : '' ?>>
                Согласен с контрактом
            </label>
            <?= !empty($errors['contract_agreed']) ? '<div class="error-message">'.htmlspecialchars($errors['contract_agreed']).'</div>' : '' ?>
            
            <button type="submit">Сохранить изменения</button>
            <a href="admin.php" class="cancel-button">Отмена</a>
        </form>
    </div>
</body>
</html>