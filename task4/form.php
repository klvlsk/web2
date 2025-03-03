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
if (!empty($messages)) {
  print('<div id="messages">');
  // Выводим все сообщения.
  foreach ($messages as $message) {
    print($message);
  }
  print('</div>');
}

// Далее выводим форму, отмечая элементы с ошибками классом error
// и задавая начальные значения элементов ранее сохраненными.
?>

    <form action="" method="POST">
      <label>ФИО:</label>
      <input name="fio" <?php if ($errors['fio']) {print 'class="error"';} ?> value="<?php print $values['fio']; ?>" /><br>

      <label>Телефон:</label>
      <input name="phone" <?php if ($errors['phone']) {print 'class="error"';} ?> value="<?php print $values['phone']; ?>" /><br>

      <label>Email:</label>
      <input name="email" <?php if ($errors['email']) {print 'class="error"';} ?> value="<?php print $values['email']; ?>" /><br>

      <label>Дата рождения:</label>
      <input name="birth_date" <?php if ($errors['birth_date']) {print 'class="error"';} ?> value="<?php print $values['birth_date']; ?>" /><br>

      <label>Пол:</label>
      <input type="radio" name="gender" value="male" <?php if ($values['gender'] === 'male') {print 'checked';} ?>> Мужской
      <input type="radio" name="gender" value="female" <?php if ($values['gender'] === 'female') {print 'checked';} ?>> Женский<br>

      <label>Любимый язык программирования:</label>
      <select name="languages[]" multiple>
        <?php $languagesOptions = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskel', 'Clojure', 'Prolog', 'Scala', 'Go']; ?>
        <?php foreach ($languagesOptions as $key => $language): ?>
          <option value="<?php echo $key + 1; ?>" <?php if (in_array($key + 1, $values['languages'])) {print 'selected';} ?>><?php echo $language; ?></option>
        <?php endforeach; ?>
      </select><br>

      <label>Биография:</label>
      <textarea name="biography" <?php if ($errors['biography']) {print 'class="error"';} ?>><?php print $values['biography']; ?></textarea><br>

      <label>
        <input type="checkbox" name="contract_agreed" <?php if ($values['contract_agreed']) {print 'checked';} ?>> С контрактом ознакомлен(а)
      </label><br>

      <input type="submit" value="Сохранить">
    </form>
  </body>
</html>