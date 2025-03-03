<html>
  <head>
    <style>
      /* Сообщения об ошибках и поля с ошибками выводим с красным бордюром. */
      .error {
        border: 2px solid red !important; /* Яркая подсветка для полей с ошибками */
      }

      /* Стили для сообщений об ошибках */
      #messages {
        margin-bottom: 20px; /* Отступ снизу */
      }

      .error-message {
        color: red; /* Цвет текста */
        margin-bottom: 10px; /* Отступ снизу */
        padding: 10px; /* Внутренние отступы */
        background-color: #ffe6e6; /* Фоновый цвет */
        border: 1px solid red; /* Граница */
        border-radius: 4px; /* Скругление углов */
      }

      /* Стили для сообщений об успешном сохранении */
      .success-message {
        color: green; /* Цвет текста */
        margin-bottom: 10px; /* Отступ снизу */
        padding: 10px; /* Внутренние отступы */
        background-color: #e6ffe6; /* Фоновый цвет */
        border: 1px solid green; /* Граница */
        border-radius: 4px; /* Скругление углов */
      }
    </style>
    <link rel="stylesheet" href="styles.css">
  </head>
  <body>

    <main>
      <div class="change">
        <div id="form">
          <?php if (!empty($messages)): ?>
            <div id="messages">
              <?php foreach ($messages as $message): ?>
                <?php echo $message; ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form action="" method="POST">
            <label>ФИО:</label>
            <input name="fio" <?php if ($errors['fio']) {print 'class="error"';} ?> value="<?php print $values['fio']; ?>" /><br>

            <label>Телефон:</label>
            <input name="phone" <?php if ($errors['phone']) {print 'class="error"';} ?> value="<?php print $values['phone']; ?>" /><br>

            <label>Email:</label>
            <input name="email" <?php if ($errors['email']) {print 'class="error"';} ?> value="<?php print $values['email']; ?>" /><br>

            <label>Дата рождения:</label>
            <input type="date" name="birth_date" <?php if ($errors['birth_date']) {print 'class="error"';} ?> value="<?php print $values['birth_date']; ?>" /><br>

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
        </div>
      </div>
    </main>
  </body>
</html>