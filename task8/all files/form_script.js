document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('application-form');
    const resultDiv = document.getElementById('form-result');
    
    // Проверяем, есть ли данные для редактирования
    const urlParams = new URLSearchParams(window.location.search);
    const login = urlParams.get('login');
    const password = urlParams.get('password');
    
    if (login) {
        // Загружаем данные пользователя для редактирования
        loadUserData(login);
    }
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const selectedLanguages = Array.from(form.querySelectorAll('#languages option:checked')).map(opt => opt.value);
        
        const data = {
            full_name: formData.get('full_name'),
            phone: formData.get('phone'),
            email: formData.get('email'),
            birth_date: formData.get('birth_date'),
            gender: formData.get('gender'),
            languages: selectedLanguages,
            biography: formData.get('biography'),
            contract_agreed: formData.get('contract_agreed') === 'on'
        };
        
        if (!validateForm(data)) {
            return;
        }
        
        if (login) {
            // Редактирование существующего пользователя
            updateUser(data, login);
        } else {
            // Создание нового пользователя
            createUser(data);
        }
    });
    
    function loadUserData(login) {
        fetch('api.php?action=get_user&login=' + encodeURIComponent(login), {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to load user data');
            return response.json();
        })
        .then(user => {
            if (user.success) {
                // Заполняем форму данными пользователя
                form.elements.full_name.value = user.data.full_name || '';
                form.elements.phone.value = user.data.phone || '';
                form.elements.email.value = user.data.email || '';
                form.elements.birth_date.value = user.data.birth_date || '';
                
                if (user.data.gender) {
                    const genderRadio = form.querySelector(`input[name="gender"][value="${user.data.gender}"]`);
                    if (genderRadio) genderRadio.checked = true;
                }
                
                if (user.data.languages) {
                    Array.from(form.elements['languages[]'].options).forEach(option => {
                        option.selected = user.data.languages.includes(parseInt(option.value));
                    });
                }
                
                form.elements.biography.value = user.data.biography || '';
                form.elements.contract_agreed.checked = user.data.contract_agreed || false;
                
                resultDiv.textContent = 'Режим редактирования. Вы можете изменить свои данные.';
                resultDiv.className = 'result info';
            } else {
                throw new Error(user.message || 'Failed to load user data');
            }
        })
        .catch(error => {
            resultDiv.textContent = 'Ошибка загрузки данных: ' + error.message;
            resultDiv.className = 'result error';
        });
    }
    
    function createUser(data) {
        fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = 'Форма успешно отправлена! Ваши данные для входа: Логин: ' + 
                    data.login + ', Пароль: ' + data.password + '. Сохраните их!<br>' +
                    '<a href="login.php" class="btn btn-primary mt-2">Войти в систему</a>';
                resultDiv.className = 'result success';
                
                form.reset();
            } else {
                showErrors(data.errors || { message: data.message });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.textContent = 'Ошибка сети. Попробуйте позже.';
            resultDiv.className = 'result error';
        });
    }
    
    function updateUser(data, login) {
        fetch('api.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Basic ' + btoa(login + ':' + prompt('Введите ваш пароль для подтверждения:'))
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.textContent = 'Данные успешно обновлены!';
                resultDiv.className = 'result success';
            } else {
                showErrors(data.errors || { message: data.message });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.textContent = 'Ошибка сети. Попробуйте позже.';
            resultDiv.className = 'result error';
        });
    }
    
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
    
    function showErrors(errors) {
        const resultDiv = document.getElementById('form-result');
        resultDiv.textContent = 'Ошибка: ' + (errors.message || 'Неизвестная ошибка');
        resultDiv.className = 'result error';
        
        // Отображение ошибок сервера
        if (errors) {
            for (const field in errors) {
                const errorElement = document.getElementById(`${field}_error`);
                if (errorElement) {
                    errorElement.textContent = errors[field];
                }
            }
        }
    }
});