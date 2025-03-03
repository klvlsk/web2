<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Инициализируем массивы, чтобы избежать ошибок
$errors = $_SESSION['errors'] ?? [];
$values = $_SESSION['values'] ?? [];
$messages = $_SESSION['messages'] ?? [];

unset($_SESSION['errors'], $_SESSION['values'], $_SESSION['messages']);

// Подключаем стили
echo '<link rel="stylesheet" href="styles.css">';
?>

<html>
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

            <label>ФИО:</label>
            <input type="text" name="fio" required pattern="[A-Za-zА-Яа-я\s]{1,150}" maxlength="150" <?php if (!empty($errors['fio'])) {print 'class="error"';} ?> value="<?php echo htmlspecialchars($values['fio'] ?? ''); ?>"><br>

            <label>Телефон:</label>
            <input type="tel" name="phone" required pattern="\+7\d{10}" <?php if (!empty($errors['phone'])) {print 'class="error"';} ?> value="<?php echo htmlspecialchars($values['phone'] ?? ''); ?>"><br>

            <label>Email:</label>
            <input type="email" name="email" required <?php if (!empty($errors['email'])) {print 'class="error"';} ?> value="<?php echo htmlspecialchars($values['email'] ?? ''); ?>"><br>

            <label>Дата рождения:</label>
            <input type="date" name="birth_date" required <?php if (!empty($errors['birth_date'])) {print 'class="error"';} ?> value="<?php echo htmlspecialchars($values['birth_date'] ?? ''); ?>"><br>

            <label>Пол:</label>
            <input type="radio" name="gender" value="male" required <?php if (($values['gender'] ?? '') === 'male') {print 'checked';} ?>> Мужской
            <input type="radio" name="gender" value="female" <?php if (($values['gender'] ?? '') === 'female') {print 'checked';} ?>> Женский<br>

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
            </select><br>

            <label>Биография:</label>
            <textarea name="biography" required maxlength="500" <?php if (!empty($errors['biography'])) {print 'class="error"';} ?>><?php echo htmlspecialchars($values['biography'] ?? ''); ?></textarea><br>

            <label>
              <input type="checkbox" name="contract_agreed" required <?php if (!empty($values['contract_agreed'])) {print 'checked';} ?>> С контрактом ознакомлен(а)
            </label><br>

            <input type="submit" value="Сохранить">
          </div>
        </div>
      </form>
    </main>
  </body>
</html>