document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('application-form');
    
    // Валидация на клиенте
    function validateForm(data) {
        let isValid = true;
        
        // Очищаем предыдущие ошибки
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
        
        // Валидация ФИО
        if (!data.full_name || !/^[A-Za-zА-Яа-я\s]{1,150}$/u.test(data.full_name)) {
            document.getElementById('full_name_error').textContent = 'Заполните корректно ФИО';
            isValid = false;
        }
        
        // Валидация телефона
        if (!data.phone || !/^\+7\d{10}$/.test(data.phone)) {
            document.getElementById('phone_error').textContent = 'Формат: +7XXXXXXXXXX';
            isValid = false;
        }
        
        // Валидация email
        if (!data.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
            document.getElementById('email_error').textContent = 'Заполните корректно email';
            isValid = false;
        }
        
        // Валидация даты рождения
        if (!data.birth_date) {
            document.getElementById('birth_date_error').textContent = 'Укажите дату рождения';
            isValid = false;
        }
        
        // Валидация пола
        if (!data.gender) {
            document.getElementById('gender_error').textContent = 'Выберите пол';
            isValid = false;
        }
        
        // Валидация языков
        if (!data.languages || data.languages.length === 0) {
            document.getElementById('languages_error').textContent = 'Выберите хотя бы один язык';
            isValid = false;
        }
        
        // Валидация биографии
        if (!data.biography || data.biography.length > 500) {
            document.getElementById('biography_error').textContent = 'Биография обязательна (макс. 500 символов)';
            isValid = false;
        }
        
        // Валидация согласия
        if (!data.contract_agreed) {
            document.getElementById('contract_agreed_error').textContent = 'Необходимо согласие';
            isValid = false;
        }
        
        return isValid;
    }
    
    // Обработка отправки формы
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const data = {
            full_name: formData.get('full_name'),
            phone: formData.get('phone'),
            email: formData.get('email'),
            birth_date: formData.get('birth_date'),
            gender: formData.get('gender'),
            languages: Array.from(document.querySelectorAll('#languages option:checked')).map(opt => opt.value),
            biography: formData.get('biography'),
            contract_agreed: formData.get('contract_agreed') === 'on'
        };
        
        // Валидация на клиенте
        if (!validateForm(data)) {
            return;
        }
        
        // Отправка AJAX
        fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('form-result');
            if (data.success) {
                resultDiv.textContent = 'Форма успешно отправлена!';
                resultDiv.className = 'result success';
                if (data.login && data.password) {
                    resultDiv.textContent += ` Ваши данные для входа: Логин: ${data.login}, Пароль: ${data.password}`;
                }
                form.reset();
            } else {
                resultDiv.textContent = 'Ошибка: ' + (data.message || 'Неизвестная ошибка');
                resultDiv.className = 'result error';
                
                // Отображение ошибок сервера
                if (data.errors) {
                    for (const field in data.errors) {
                        const errorElement = document.getElementById(`${field}_error`);
                        if (errorElement) {
                            errorElement.textContent = data.errors[field];
                        }
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('form-result').textContent = 'Ошибка сети. Попробуйте позже.';
            document.getElementById('form-result').className = 'result error';
        });
    });
    
    // Фоллбек для браузеров без JavaScript
    form.setAttribute('action', 'api.php');
    form.setAttribute('method', 'POST');
});