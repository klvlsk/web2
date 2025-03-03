<?php
// Подключаем стили
echo '<link rel="stylesheet" href="styles.css">';
?>

<html>
  <head>
    <style>
      /* Сообщения об ошибках и поля с ошибками выводим с красным бордюром. */
      .error {
        border: 2px solid red;
      }
    </style>
  </head>
  <body>

<?php
// Проверяем, есть ли сообщения об ошибках
if (!empty($messages)) {
  print('<div id="messages">');
  // Выводим все сообщения.
  foreach ($messages as $message) {
    print($message);
  }
  print('</div>');
}
?>

<main>
  <div class="change">
    <div id="form">
      <form method="post" action="index.php">
        <label>ФИО:</label>
        <input type="text" name="fio" required pattern="[A-Za-zА-Яа-я\s]{1,150}" maxlength="150" <?php if ($errors['fio']) {print 'class="error"';} ?> value="<?php print $values['fio']; ?>"><br>

        <label>Телефон:</label>
        <input type="tel" name="phone" required pattern="\+7\d{10}" <?php if ($errors['phone']) {print 'class="error"';} ?> value="<?php print $values['phone']; ?>"><br>

        <label>Email:</label>
        <input type="email" name="email" required <?php if ($errors['email']) {print 'class="error"';} ?> value="<?php print $values['email']; ?>"><br>

        <label>Дата рождения:</label>
        <input type="date" name="birth_date" required <?php if ($errors['birth_date']) {print 'class="error"';} ?> value="<?php print $values['birth_date']; ?>"><br>

        <label>Пол:</label>
        <input type="radio" name="gender" value="male" required <?php if ($values['gender'] === 'male') {print 'checked';} ?>> Мужской
        <input type="radio" name="gender" value="female" <?php if ($values['gender'] === 'female') {print 'checked';} ?>> Женский<br>

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
        <textarea name="biography" required maxlength="500" <?php if ($errors['biography']) {print 'class="error"';} ?>><?php print $values['biography']; ?></textarea><br>

        <label>
          <input type="checkbox" name="contract_agreed" required <?php if ($values['contract_agreed']) {print 'checked';} ?>> С контрактом ознакомлен(а)
        </label><br>

        <input type="submit" value="Сохранить">
      </form>
    </div>
  </div>
</main>
  </body>
</html>