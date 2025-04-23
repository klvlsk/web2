<?php
require_once 'DatabaseRepository.php';
require_once 'template_helpers.php';

$errors = is_array($_SESSION['errors'] ?? null) ? $_SESSION['errors'] : [];
$values = $_SESSION['values'] ?? [];
$messages = $_SESSION['messages'] ?? [];

unset($_SESSION['errors'], $_SESSION['values'], $_SESSION['messages']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main>
        <form action="index.php" method="POST" novalidate>
            <div class="change">
                <div id="form">
                    <?php if (!empty($messages)): ?>
                        <div id="messages">
                            <?php foreach ($messages as $message): ?>
                                <div class="success">
                                    <?= $message['html'] ?? htmlspecialchars($message) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?= renderFormField('text', 'fio', 'ФИО', $errors, $values, [
                        'required' => '',
                        'pattern' => '[A-Za-zА-Яа-я\s]{1,150}',
                        'maxlength' => '150'
                    ]) ?>

                    <?= renderFormField('tel', 'phone', 'Телефон', $errors, $values, [
                        'required' => '',
                        'pattern' => '\+7\d{10}'
                    ]) ?>

                    <?= renderFormField('email', 'email', 'Email', $errors, $values, [
                        'required' => ''
                    ]) ?>

                    <?= renderFormField('date', 'birth_date', 'Дата рождения', $errors, $values, [
                        'required' => ''
                    ]) ?>

                    <label>Пол:</label>
                    <?= renderRadioField('gender', 'male', 'Мужской', $values) ?>
                    <?= renderRadioField('gender', 'female', 'Женский', $values) ?>
                    <?php if (!empty($errors['gender'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['gender']) ?></div>
                    <?php endif; ?>

                    <label>Любимый язык программирования:</label>
                    <?= renderSelectLanguages($values['languages'] ?? []) ?>
                    <?php if (!empty($errors['languages'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['languages']) ?></div>
                    <?php endif; ?>

                    <?= renderTextarea('biography', 'Биография', $errors, $values, [
                        'required' => '',
                        'maxlength' => '500'
                    ]) ?>

                    <label>
                        <input type="checkbox" name="contract_agreed" required 
                            <?= !empty($values['contract_agreed']) ? 'checked' : '' ?>>
                        С контрактом ознакомлен(а)
                    </label>
                    <?php if (!empty($errors['contract_agreed'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['contract_agreed']) ?></div>
                    <?php endif; ?>

                    <input type="submit" value="Сохранить">
                </div>
            </div>
        </form>
    </main>
</body>
</html>