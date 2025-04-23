<?php
function getFormValues() {
    $values = [];
    $fields = ['fio', 'phone', 'email', 'birth_date', 'gender', 'biography', 'contract_agreed'];
    
    foreach ($fields as $field) {
        $values[$field] = $_POST[$field] ?? '';
    }
    
    $values['languages'] = $_POST['languages'] ?? [];
    return $values;
}

function validateForm($values) {
    $errors = [];
    
    if (empty($values['fio']) || !preg_match('/^[A-Za-zА-Яа-я\s]{1,150}$/u', $values['fio'])) {
        $errors['fio'] = 'Заполните корректно ФИО (только буквы и пробелы, не более 150 символов).';
    }
    
    if (empty($values['phone']) || !preg_match('/^\+7\d{10}$/', $values['phone'])) {
        $errors['phone'] = 'Заполните корректно телефон (формат: +7XXXXXXXXXX).';
    }
    
    if (empty($values['email']) || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Заполните корректно email.';
    }
    
    if (empty($values['birth_date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $values['birth_date'])) {
        $errors['birth_date'] = 'Заполните корректно дату рождения (формат: YYYY-MM-DD).';
    }
    
    if (empty($values['gender']) || !in_array($values['gender'], ['male', 'female'])) {
        $errors['gender'] = 'Выберите пол.';
    }
    
    if (empty($values['languages']) || !is_array($values['languages'])) {
        $errors['languages'] = 'Выберите хотя бы один язык программирования.';
    }
    
    if (empty(trim($values['biography'])) || strlen($values['biography']) > 500) {
        $errors['biography'] = 'Заполните биографию (не более 500 символов).';
    }
    
    if (empty($values['contract_agreed'])) {
        $errors['contract_agreed'] = 'Необходимо согласие с контрактом.';
    }
    
    return $errors;
}

function saveUserData($values, $isEdit = false, $userId = null) {
    $db = getDBConnection();
    
    try {
        $db->beginTransaction();
        
        if ($isEdit && $userId) {
            // Для существующего пользователя получаем текущие логин и пароль
            $stmt = $db->prepare("SELECT login, pass FROM application WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            // Обновляем данные пользователя
            $stmt = $db->prepare("
                UPDATE application 
                SET full_name=?, phone=?, email=?, birth_date=?, gender=?, biography=?, contract_agreed=? 
                WHERE id=?
            ");
            $stmt->execute([
                $values['fio'], 
                $values['phone'], 
                $values['email'], 
                $values['birth_date'], 
                $values['gender'], 
                $values['biography'], 
                $values['contract_agreed'] ? 1 : 0, 
                $userId
            ]);
            
            // Возвращаем текущие логин и пароль
            $db->commit();
            return ['login' => $user['login'], 'pass' => $user['pass']];
        } else {
            // Создаем нового пользователя
            $login = uniqid();
            $pass = substr(md5(rand()), 0, 8);
            $pass_hash = md5($pass);
            
            $stmt = $db->prepare("
                INSERT INTO application 
                (full_name, phone, email, birth_date, gender, biography, contract_agreed, login, pass) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $values['fio'], 
                $values['phone'], 
                $values['email'], 
                $values['birth_date'], 
                $values['gender'], 
                $values['biography'], 
                $values['contract_agreed'] ? 1 : 0, 
                $login, 
                $pass_hash
            ]);
            
            $userId = $db->lastInsertId();
            
            // Обновляем языки программирования
            $db->prepare("DELETE FROM application_languages WHERE application_id = ?")->execute([$userId]);
            foreach ($values['languages'] as $language_id) {
                $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)")
                   ->execute([$userId, $language_id]);
            }
            
            $db->commit();
            return ['login' => $login, 'pass' => $pass];
        }
    } catch (PDOException $e) {
        $db->rollBack();
        return false;
    }
}