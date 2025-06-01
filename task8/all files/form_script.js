document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('application-form');
    const resultDiv = document.getElementById('form-result');
    
    // Проверяем, есть ли данные для редактирования
    const urlParams = new URLSearchParams(window.location.search);
    const login = urlParams.get('login');
    const logout = urlParams.get('logout');
    
    if (logout === '1') {
        // Очищаем параметры URL без перезагрузки
        history.replaceState(null, '', window.location.pathname);
    } else if (login) {
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
        fetch(`api.php?login=${encodeURIComponent(login)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to load user data');
            return response.json();
        })
        .then(response => {
            if (response.success) {
                const user = response.data;
                // Заполняем форму данными пользователя
                form.elements.full_name.value = user.full_name || '';
                form.elements.phone.value = user.phone || '';
                form.elements.email.value = user.email || '';
                form.elements.birth_date.value = user.birth_date || '';
                
                if (user.gender) {
                    const genderRadio = form.querySelector(`input[name="gender"][value="${user.gender}"]`);
                    if (genderRadio) genderRadio.checked = true;
                }
                
                if (user.languages) {
                    Array.from(form.elements['languages[]'].options).forEach(option => {
                        option.selected = user.languages.includes(parseInt(option.value));
                    });
                }
                
                form.elements.biography.value = user.biography || '';
                form.elements.contract_agreed.checked = user.contract_agreed || false;
                
                resultDiv.textContent = 'Режим редактирования. Вы можете изменить свои данные.';
                resultDiv.className = 'result info';
            } else {
                throw new Error(response.message || 'Failed to load user data');
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
        .then(response => {
            if (response.success) {
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        Форма успешно отправлена!<br>
                        Ваши данные для входа:<br>
                        Логин: ${response.login}<br>
                        Пароль: ${response.password}<br>
                        <a href="${response.profile_url}" class="btn btn-primary mt-2">Перейти к редактированию профиля</a>
                    </div>
                `;
                resultDiv.className = 'result success';
            } else {
                showErrors(response.errors || { message: response.message });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.textContent = 'Ошибка сети. Попробуйте позже.';
            resultDiv.className = 'result error';
        });
    }
    
    function updateUser(data, login) {
        const password = prompt('Введите ваш пароль для подтверждения:');
        if (!password) return;
        
        fetch('api.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Basic ' + btoa(login + ':' + password)
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        Данные успешно обновлены!<br>
                        <a href="index.php?logout=1" class="btn btn-secondary mt-2">Выйти</a>
                    </div>
                `;
                resultDiv.className = 'result success';
            } else {
                showErrors(response.errors || { message: response.message });
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