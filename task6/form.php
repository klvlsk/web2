<?php
$errors = $_SESSION['errors'] ?? [];
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

                    <?= renderFormField('text', 'fio', 'ФИО', '[A-Za-zА-Яа-я\s]{1,150}', 150, $errors, $values) ?>
                    <?= renderFormField('tel', 'phone', 'Телефон', '\+7\d{10}', null, $errors, $values) ?>
                    <?= renderFormField('email', 'email', 'Email', null, null, $errors, $values) ?>
                    <?= renderFormField('date', 'birth_date', 'Дата рождения', null, null, $errors, $values) ?>

                    <label>Пол:</label>
                    <?= renderRadioField('gender', 'male', 'Мужской', $values) ?>
                    <?= renderRadioField('gender', 'female', 'Женский', $values) ?>
                    <?= renderError('gender', $errors) ?>

                    <label>Любимый язык программирования:</label>
                    <select name="languages[]" multiple required>
                        <?php foreach (getProgrammingLanguages() as $key => $value): ?>
                            <option value="<?= $key ?>" <?= in_array($key, $values['languages'] ?? []) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($value) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?= renderError('languages', $errors) ?>

                    <label>Биография:</label>
                    <textarea name="biography" required maxlength="500" <?= !empty($errors['biography']) ? 'class="error"' : '' ?>><?= 
                        trim(htmlspecialchars($values['biography'] ?? '')) ?>
                    </textarea>
                    <?= renderError('biography', $errors) ?>

                    <label>
                        <input type="checkbox" name="contract_agreed" required <?= !empty($values['contract_agreed']) ? 'checked' : '' ?>>
                        С контрактом ознакомлен(а)
                    </label>
                    <?= renderError('contract_agreed', $errors) ?>

                    <input type="submit" value="Сохранить">
                </div>
            </div>
        </form>
    </main>
</body>
</html>

<?php
function renderFormField($type, $name, $label, $pattern = null, $maxlength = null, $errors, $values) {
    $html = '<label>'.$label.':</label>';
    $html .= '<input type="'.$type.'" name="'.$name.'" required ';
    
    if ($pattern) $html .= 'pattern="'.$pattern.'" ';
    if ($maxlength) $html .= 'maxlength="'.$maxlength.'" ';
    if (!empty($errors[$name])) $html .= 'class="error" ';
    
    $html .= 'value="'.htmlspecialchars($values[$name] ?? '').'">';
    $html .= renderError($name, $errors);
    
    return $html;
}

function renderRadioField($name, $value, $label, $values) {
    $checked = ($values[$name] ?? '') === $value ? 'checked' : '';
    return '<input type="radio" name="'.$name.'" value="'.$value.'" '.$checked.'> '.$label;
}

function renderError($name, $errors) {
    return !empty($errors[$name]) ? '<div class="error-message">'.$errors[$name].'</div>' : '';
}

function getProgrammingLanguages() {
    return [
        1 => 'Pascal',
        2 => 'C',
        3 => 'C++',
        4 => 'JavaScript',
        5 => 'PHP',
        6 => 'Python',
        7 => 'Java',
        8 => 'Haskel',
        9 => 'Clojure',
        10 => 'Prolog',
        11 => 'Scala',
        12 => 'Go'
    ];
}