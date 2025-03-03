<?php
header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  $messages = array();

  if (!empty($_COOKIE['save'])) {
    setcookie('save', '', 100000);
    $messages[] = 'Спасибо, результаты сохранены.';
  }

  $errors = array();
  $errors['fio'] = !empty($_COOKIE['fio_error']);
  $errors['phone'] = !empty($_COOKIE['phone_error']);
  $errors['email'] = !empty($_COOKIE['email_error']);
  $errors['birth_date'] = !empty($_COOKIE['birth_date_error']);
  $errors['gender'] = !empty($_COOKIE['gender_error']);
  $errors['languages'] = !empty($_COOKIE['languages_error']);
  $errors['biography'] = !empty($_COOKIE['biography_error']);
  $errors['contract_agreed'] = !empty($_COOKIE['contract_agreed_error']);

  if ($errors['fio']) {
    setcookie('fio_error', '', 100000);
    setcookie('fio_value', '', 100000);
    $messages[] = '<div class="error">Заполните имя.</div>';
  }
  if ($errors['phone']) {
    setcookie('phone_error', '', 100000);
    setcookie('phone_value', '', 100000);
    $messages[] = '<div class="error">Заполните телефон.</div>';
  }
  if ($errors['email']) {
    setcookie('email_error', '', 100000);
    setcookie('email_value', '', 100000);
    $messages[] = '<div class="error">Заполните email.</div>';
  }
  if ($errors['birth_date']) {
    setcookie('birth_date_error', '', 100000);
    setcookie('birth_date_value', '', 100000);
    $messages[] = '<div class="error">Заполните дату рождения.</div>';
  }
  if ($errors['gender']) {
    setcookie('gender_error', '', 100000);
    setcookie('gender_value', '', 100000);
    $messages[] = '<div class="error">Выберите пол.</div>';
  }
  if ($errors['languages']) {
    setcookie('languages_error', '', 100000);
    setcookie('languages_value', '', 100000);
    $messages[] = '<div class="error">Выберите хотя бы один язык программирования.</div>';
  }
  if ($errors['biography']) {
    setcookie('biography_error', '', 100000);
    setcookie('biography_value', '', 100000);
    $messages[] = '<div class="error">Заполните биографию.</div>';
  }
  if ($errors['contract_agreed']) {
    setcookie('contract_agreed_error', '', 100000);
    setcookie('contract_agreed_value', '', 100000);
    $messages[] = '<div class="error">Необходимо согласие с контрактом.</div>';
  }

  $values = array();
  $values['fio'] = empty($_COOKIE['fio_value']) ? '' : $_COOKIE['fio_value'];
  $values['phone'] = empty($_COOKIE['phone_value']) ? '' : $_COOKIE['phone_value'];
  $values['email'] = empty($_COOKIE['email_value']) ? '' : $_COOKIE['email_value'];
  $values['birth_date'] = empty($_COOKIE['birth_date_value']) ? '' : $_COOKIE['birth_date_value'];
  $values['gender'] = empty($_COOKIE['gender_value']) ? '' : $_COOKIE['gender_value'];
  $values['languages'] = empty($_COOKIE['languages_value']) ? array() : explode(',', $_COOKIE['languages_value']);
  $values['biography'] = empty($_COOKIE['biography_value']) ? '' : $_COOKIE['biography_value'];
  $values['contract_agreed'] = empty($_COOKIE['contract_agreed_value']) ? '' : $_COOKIE['contract_agreed_value'];

  include('form.php');
} else {
  $errors = FALSE;

  if (empty($_POST['fio'])) {
    setcookie('fio_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  setcookie('fio_value', $_POST['fio'], time() + 30 * 24 * 60 * 60);

  if (empty($_POST['phone'])) {
    setcookie('phone_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  setcookie('phone_value', $_POST['phone'], time() + 30 * 24 * 60 * 60);

  if (empty($_POST['email'])) {
    setcookie('email_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  setcookie('email_value', $_POST['email'], time() + 30 * 24 * 60 * 60);

  if (empty($_POST['birth_date'])) {
    setcookie('birth_date_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  setcookie('birth_date_value', $_POST['birth_date'], time() + 30 * 24 * 60 * 60);

  if (empty($_POST['gender'])) {
    setcookie('gender_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  setcookie('gender_value', $_POST['gender'], time() + 30 * 24 * 60 * 60);

  if (empty($_POST['languages'])) {
    setcookie('languages_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  setcookie('languages_value', implode(',', $_POST['languages']), time() + 30 * 24 * 60 * 60);

  if (empty($_POST['biography'])) {
    setcookie('biography_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  setcookie('biography_value', $_POST['biography'], time() + 30 * 24 * 60 * 60);

  if (empty($_POST['contract_agreed'])) {
    setcookie('contract_agreed_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  setcookie('contract_agreed_value', $_POST['contract_agreed'], time() + 30 * 24 * 60 * 60);

  if ($errors) {
    header('Location: index.php');
    exit();
  } else {
    setcookie('fio_error', '', 100000);
    setcookie('phone_error', '', 100000);
    setcookie('email_error', '', 100000);
    setcookie('birth_date_error', '', 100000);
    setcookie('gender_error', '', 100000);
    setcookie('languages_error', '', 100000);
    setcookie('biography_error', '', 100000);
    setcookie('contract_agreed_error', '', 100000);
  }

  // Подключение к базе данных
  $user = 'u68596';
  $pass = '2859691';
  $db = new PDO('mysql:host=localhost;dbname=u68596', $user, $pass, [
      PDO::ATTR_PERSISTENT => true,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
  ]);

  try {
    // Вставка данных в таблицу application
    $stmt = $db->prepare("INSERT INTO application (full_name, phone, email, birth_date, gender, biography, contract_agreed) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['fio'],
        $_POST['phone'],
        $_POST['email'],
        $_POST['birth_date'],
        $_POST['gender'],
        $_POST['biography'],
        $_POST['contract_agreed'] ? 1 : 0
    ]);
    $application_id = $db->lastInsertId();

    // Вставка выбранных языков программирования
    foreach ($_POST['languages'] as $language_id) {
        $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
        $stmt->execute([$application_id, $language_id]);
    }
  } catch (PDOException $e) {
    print('Ошибка при сохранении данных: ' . $e->getMessage());
    exit();
  }

  setcookie('save', '1');
  header('Location: index.php');
}