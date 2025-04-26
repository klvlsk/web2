<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Форма</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main>
        <?php if (SessionManager::isLoggedIn()): ?>
            <div class="edit-notice">
                Режим редактирования. 
                <a href="index.php?logout=1" class="logout-link">Выйти и создать нового пользователя</a>
            </div>
        <?php endif; ?>
        
        <?php if ($messages): ?>
            <div class="messages">
                <div class="message <?= $messages['type'] ?>"><?= $messages['message'] ?></div>
            </div>
        <?php endif; ?>

        <form action="index.php" method="POST" novalidate>
            <?= FormRenderer::renderField('text', 'fio', 'ФИО', $errors, $values, [
                'required' => '',
                'pattern' => '[A-Za-zА-Яа-я\s]{1,150}',
                'maxlength' => '150'
            ]) ?>

            <?= FormRenderer::renderField('tel', 'phone', 'Телефон', $errors, $values, [
                'required' => '',
                'pattern' => '\+7\d{10}'
            ]) ?>

            <?= FormRenderer::renderField('email', 'email', 'Email', $errors, $values, [
                'required' => ''
            ]) ?>

            <?= FormRenderer::renderField('date', 'birth_date', 'Дата рождения', $errors, $values, [
                'required' => ''
            ]) ?>

            <label>Пол:</label>
            <?= FormRenderer::renderRadio('gender', 'male', 'Мужской', $values) ?>
            <?= FormRenderer::renderRadio('gender', 'female', 'Женский', $values) ?>
            <?= !empty($errors['gender']) ? '<div class="error-message">'.htmlspecialchars($errors['gender']).'</div>' : '' ?>

            <label>Любимый язык программирования:</label>
            <?= FormRenderer::renderSelectLanguages($values['languages'] ?? []) ?>
            <?= !empty($errors['languages']) ? '<div class="error-message">'.htmlspecialchars($errors['languages']).'</div>' : '' ?>

            <?= FormRenderer::renderField('textarea', 'biography', 'Биография', $errors, $values, [
                'required' => '',
                'maxlength' => '500'
            ]) ?>

            <label>
                <input type="checkbox" name="contract_agreed" required 
                    <?= !empty($values['contract_agreed']) ? 'checked' : '' ?>>
                С контрактом ознакомлен(а)
            </label>
            <?= !empty($errors['contract_agreed']) ? '<div class="error-message">'.htmlspecialchars($errors['contract_agreed']).'</div>' : '' ?>

            <input type="submit" value="Сохранить">
        </form>
    </main>
</body>
</html>