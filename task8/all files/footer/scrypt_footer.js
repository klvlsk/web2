const openFormBtn = document.getElementById('open-form-btn');
const form = document.getElementById('feedback-form');
const resultDiv = document.getElementById('feedback-result');

const localStorageKey = 'feedbackFormValues';

function saveFormData() {
    const formData = {
        name: form.name.value,
        phone: form.phone.value,
        email: form.email.value,
        message: form.message.value,
    };
    localStorage.setItem(localStorageKey, JSON.stringify(formData));
}

function loadFormData() {
    const savedData = localStorage.getItem(localStorageKey);
    if (savedData) {
        const { name, phone, email, message } = JSON.parse(savedData);
        form.name.value = name || '';
        form.phone.value = phone || '';
        form.email.value = email || '';
        form.message.value = message || '';
    }
}

function clearFormData() {
    localStorage.removeItem(localStorageKey);
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    resultDiv.textContent = 'Отправка...';
    const formData = new FormData(form);

    try {
        const response = await fetch('https://formcarry.com/s/g7ga5yAahTs', {
            method: 'POST',
            body: formData,
        });

            resultDiv.textContent = 'Заявка успешно отправлена!';
            clearFormData();
            form.reset();
        
    } catch (error) {
        resultDiv.textContent = 'Ошибка сети. Попробуйте позже.';
    }
});

form.addEventListener('input', saveFormData);
