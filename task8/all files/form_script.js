document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('application-form');
    const resultDiv = document.getElementById('form-result');

    // Добавляем скрытое поле для определения отключенного JavaScript
    const noJsField = document.createElement('input');
    noJsField.type = 'hidden';
    noJsField.name = 'nojs';
    noJsField.value = '0';
    form.appendChild(noJsField);

    // Проверяем параметры URL
    const urlParams = new URLSearchParams(window.location.search);
    const login = urlParams.get('login');
    const logout = urlParams.get('logout');
    
    if (logout === '1') {
        history.replaceState(null, '', window.location.pathname);
    } else if (login) {
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
            updateUser(data, login);
        } else {
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
                fillForm(response.data);
                showMessage('Режим редактирования. Вы можете изменить свои данные.', 'info');
            } else {
                throw new Error(response.message || 'Failed to load user data');
            }
        })
        .catch(error => {
            showMessage('Ошибка загрузки данных: ' + error.message, 'error');
        });
    }
    
    function fillForm(user) {
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
    }
    
    function createUser(data) {
        fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(text || 'Network response was not ok');
                });
            }
            return response.json();
        })
        .then(response => {
            if (response.success) {
                showSuccess(response);
                addLogoutButton();
            } else {
                showErrors(response.errors || { message: response.message });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Ошибка сети: ' + error.message, 'error');
        });
    }

    function addLogoutButton() {
        const logoutBtn = document.createElement('a');
        logoutBtn.href = 'index.php?logout=1';
        logoutBtn.className = 'btn btn-secondary mt-2';
        logoutBtn.textContent = 'Выйти';
        resultDiv.appendChild(logoutBtn);
    }
    
    function updateUser(data, login) {
        const password = prompt('Введите ваш пароль для подтверждения:');
        if (!password) return;
        
        fetch('api.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Basic ' + btoa(login + ':' + password)
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(response => {
            if (response.success) {
                showMessage(`
                    <div class="alert alert-success">
                        Данные успешно обновлены!<br>
                        <a href="index.php?logout=1" class="btn btn-secondary mt-2">Выйти</a>
                    </div>
                `, 'success');
            } else {
                showErrors(response.errors || { message: response.message });
            }
        })
        .catch(error => {
            showMessage('Ошибка сети: ' + error.message, 'error');
            console.error('Error:', error);
        });
    }
    
    function validateForm(data) {
        let isValid = true;
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
        
        if (!data.full_name || !/^[A-Za-zА-Яа-я\s]{1,150}$/u.test(data.full_name)) {
            document.getElementById('full_name_error').textContent = 'Заполните корректно ФИО';
            isValid = false;
        }
        
        if (!data.phone || !/^\+7\d{10}$/.test(data.phone)) {
            document.getElementById('phone_error').textContent = 'Формат: +7XXXXXXXXXX';
            isValid = false;
        }
        
        if (!data.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
            document.getElementById('email_error').textContent = 'Заполните корректно email';
            isValid = false;
        }
        
        if (!data.birth_date) {
            document.getElementById('birth_date_error').textContent = 'Укажите дату рождения';
            isValid = false;
        }
        
        if (!data.gender) {
            document.getElementById('gender_error').textContent = 'Выберите пол';
            isValid = false;
        }
        
        if (!data.languages || data.languages.length === 0) {
            document.getElementById('languages_error').textContent = 'Выберите хотя бы один язык';
            isValid = false;
        }
        
        if (!data.biography || data.biography.length > 500) {
            document.getElementById('biography_error').textContent = 'Биография обязательна (макс. 500 символов)';
            isValid = false;
        }
        
        if (!data.contract_agreed) {
            document.getElementById('contract_agreed_error').textContent = 'Необходимо согласие';
            isValid = false;
        }
        
        return isValid;
    }
    
    function showSuccess(response) {
        showMessage(`
            <div class="alert alert-success">
                Форма успешно отправлена!<br>
                Ваши данные для входа:<br>
                Логин: ${response.login}<br>
                Пароль: ${response.password}<br>
                <a href="${response.profile_url}" class="btn btn-primary mt-2">Перейти к редактированию профиля</a>
            </div>
        `, 'success');
    }
    
    function showErrors(errors) {
        if (errors.message) {
            showMessage('Ошибка: ' + errors.message, 'error');
        }
        
        for (const field in errors) {
            const errorElement = document.getElementById(`${field}_error`);
            if (errorElement) {
                errorElement.textContent = errors[field];
            }
        }
    }
    
    function showMessage(message, type) {
        resultDiv.innerHTML = typeof message === 'string' ? message : '';
        resultDiv.className = `result ${type}`;
    }
});