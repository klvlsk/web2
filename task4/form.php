<?php
// Инициализируем массивы, чтобы избежать ошибок
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
                    <?php
                    // Вывод сообщений об ошибках и успехе
                    if (!empty($messages)) {
                        print('<div id="messages">');
                        foreach ($messages as $message) {
                            // Если сообщение об успехе, добавляем класс success
                            if (strpos($message, 'Спасибо') !== false) {
                                print('<div class="success">' . $message . '</div>');
                            } else {
                                print('<div class="error">' . $message . '</div>');
                            }
                        }
                        print('</div>');
                    }
                    ?>

                    <!-- Поле ФИО -->
                    <label>ФИО:</label>
                    <input type="text" name="fio" required pattern="[A-Za-zА-Яа-я\s]{1,150}" maxlength="150" <?php if (!empty($errors['fio'])) {print 'class="error"';} ?> value="<?php echo htmlspecialchars($values['fio'] ?? ''); ?>">
                    <?php if (!empty($errors['fio'])): ?>
                        <div class="error-message"><?php echo $errors['fio']; ?></div>
                    <?php endif; ?>
                    <br>

                    <!-- Поле Телефон -->
                    <label>Телефон:</label>
                    <input type="tel" name="phone" required pattern="\+7\d{10}" <?php if (!empty($errors['phone'])) {print 'class="error"';} ?> value="<?php echo htmlspecialchars($values['phone'] ?? ''); ?>">
                    <?php if (!empty($errors['phone'])): ?>
                        <div class="error-message"><?php echo $errors['phone']; ?></div>
                    <?php endif; ?>
                    <br>

                    <!-- Поле Email -->
                    <label>Email:</label>
                    <input type="email" name="email" required <?php if (!empty($errors['email'])) {print 'class="error"';} ?> value="<?php echo htmlspecialchars($values['email'] ?? ''); ?>">
                    <?php if (!empty($errors['email'])): ?>
                        <div class="error-message"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>
                    <br>

                    <!-- Поле Дата рождения -->
                    <label>Дата рождения:</label>
                    <input type="date" name="birth_date" required <?php if (!empty($errors['birth_date'])) {print 'class="error"';} ?> value="<?php echo htmlspecialchars($values['birth_date'] ?? ''); ?>">
                    <?php if (!empty($errors['birth_date'])): ?>
                        <div class="error-message"><?php echo $errors['birth_date']; ?></div>
                    <?php endif; ?>
                    <br>

                    <!-- Поле Пол -->
                    <label>Пол:</label>
                    <input type="radio" name="gender" value="male" required <?php if (($values['gender'] ?? '') === 'male') {print 'checked';} ?>> Мужской
                    <input type="radio" name="gender" value="female" <?php if (($values['gender'] ?? '') === 'female') {print 'checked';} ?>> Женский
                    <?php if (!empty($errors['gender'])): ?>
                        <div class="error-message"><?php echo $errors['gender']; ?></div>
                    <?php endif; ?>
                    <br>

                    <!-- Поле Любимый язык программирования -->
                    <label>Любимый язык программирования:</label>
                    <select name="languages[]" multiple required>
                        <?php
                        $languages = [
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
                        foreach ($languages as $key => $value) {
                            $selected = in_array($key, $values['languages'] ?? []) ? 'selected' : '';
                            echo "<option value=\"$key\" $selected>$value</option>";
                        }
                        ?>
                    </select>
                    <?php if (!empty($errors['languages'])): ?>
                        <div class="error-message"><?php echo $errors['languages']; ?></div>
                    <?php endif; ?>
                    <br>

                    <!-- Поле Биография -->
                    <label>Биография:</label>
                    <textarea name="biography" required maxlength="500" <?php if (!empty($errors['biography'])) {print 'class="error"';} ?>><?php echo htmlspecialchars($values['biography'] ?? ''); ?></textarea>
                    <?php if (!empty($errors['biography'])): ?>
                        <div class="error-message"><?php echo $errors['biography']; ?></div>
                    <?php endif; ?>
                    <br>

                    <!-- Поле Согласие с контрактом -->
                    <label>
                        <input type="checkbox" name="contract_agreed" required <?php if (!empty($values['contract_agreed'])) {print 'checked';} ?>> С контрактом ознакомлен(а)
                    </label>
                    <?php if (!empty($errors['contract_agreed'])): ?>
                        <div class="error-message"><?php echo $errors['contract_agreed']; ?></div>
                    <?php endif; ?>
                    <br>

                    <!-- Кнопка отправки формы -->
                    <input type="submit" value="Сохранить">
                </div>
            </div>
        </form>
    </main>
</body>
</html>