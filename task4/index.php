<?php
// Устанавливаем правильную кодировку
header('Content-Type: text/html; charset=UTF-8');

// Обработка GET запроса
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Массив для временного хранения сообщений пользователю
    $messages = array();

    // Если есть кука с сообщением об успешном сохранении, выводим его
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000); // Удаляем куку
        $messages[] = 'Спасибо, результаты сохранены.';
    }

    // Массив для хранения ошибок
    $errors = array();
    $errors['fio'] = !empty($_COOKIE['fio_error']);
    $errors['phone'] = !empty($_COOKIE['phone_error']);
    $errors['email'] = !empty($_COOKIE['email_error']);
    $errors['birth_date'] = !empty($_COOKIE['birth_date_error']);
    $errors['gender'] = !empty($_COOKIE['gender_error']);
    $errors['languages'] = !empty($_COOKIE['languages_error']);
    $errors['biography'] = !empty($_COOKIE['biography_error']);
    $errors['contract_agreed'] = !empty($_COOKIE['contract_agreed_error']);

    // Массив для хранения ранее введенных значений
    $values = array();
    $values['fio'] = empty($_COOKIE['fio_value']) ? '' : $_COOKIE['fio_value'];
    $values['phone'] = empty($_COOKIE['phone_value']) ? '' : $_COOKIE['phone_value'];
    $values['email'] = empty($_COOKIE['email_value']) ? '' : $_COOKIE['email_value'];
    $values['birth_date'] = empty($_COOKIE['birth_date_value']) ? '' : $_COOKIE['birth_date_value'];
    $values['gender'] = empty($_COOKIE['gender_value']) ? '' : $_COOKIE['gender_value'];
    $values['languages'] = empty($_COOKIE['languages_value']) ? array() : json_decode($_COOKIE['languages_value'], true);
    $values['biography'] = empty($_COOKIE['biography_value']) ? '' : $_COOKIE['biography_value'];
    $values['contract_agreed'] = !empty($_COOKIE['contract_agreed_value']);

    // Подключаем форму
    include('form.php');
    exit();
}

// Обработка POST запроса
$errors = FALSE;

// Валидация данных
if (empty($_POST['fio']) || !preg_match('/^[A-Za-zА-Яа-я\s]{1,150}$/u', $_POST['fio'])) {
    setcookie('fio_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('fio_value', $_POST['fio'], time() + 30 * 24 * 60 * 60);
}

if (empty($_POST['phone']) || !preg_match('/^\+7\d{10}$/', $_POST['phone'])) {
    setcookie('phone_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('phone_value', $_POST['phone'], time() + 30 * 24 * 60 * 60);
}

if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    setcookie('email_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('email_value', $_POST['email'], time() + 30 * 24 * 60 * 60);
}

if (empty($_POST['birth_date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['birth_date'])) {
    setcookie('birth_date_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('birth_date_value', $_POST['birth_date'], time() + 30 * 24 * 60 * 60);
}

if (empty($_POST['gender']) || !in_array($_POST['gender'], ['male', 'female'])) {
    setcookie('gender_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('gender_value', $_POST['gender'], time() + 30 * 24 * 60 * 60);
}

if (empty($_POST['languages']) || !is_array($_POST['languages'])) {
    setcookie('languages_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('languages_value', json_encode($_POST['languages']), time() + 30 * 24 * 60 * 60);
}

if (empty($_POST['biography']) || strlen($_POST['biography']) > 500) {
    setcookie('biography_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('biography_value', $_POST['biography'], time() + 30 * 24 * 60 * 60);
}

if (empty($_POST['contract_agreed'])) {
    setcookie('contract_agreed_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
} else {
    setcookie('contract_agreed_value', $_POST['contract_agreed'], time() + 30 * 24 * 60 * 60);
}

// Если есть ошибки, перезагружаем страницу
if ($errors) {
    header('Location: index.php');
    exit();
}

// Удаляем куки с ошибками
setcookie('fio_error', '', 100000);
setcookie('phone_error', '', 100000);
setcookie('email_error', '', 100000);
setcookie('birth_date_error', '', 100000);
setcookie('gender_error', '', 100000);
setcookie('languages_error', '', 100000);
setcookie('biography_error', '', 100000);
setcookie('contract_agreed_error', '', 100000);

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

// Сохраняем куку с признаком успешного сохранения
setcookie('save', '1', time() + 24 * 60 * 60);

// Перенаправление на страницу с сообщением об успешном сохранении
header('Location: index.php');
?>