const form = document.querySelector('.login-form');
const password = document.getElementById('password');
const confirm = document.getElementById('password_confirmation');
const error = document.getElementById('password-error');

form.addEventListener('submit', function (e) {
    const value = password.value;
    const rules = {
        length: value.length >= 12,
        lower: /[a-z]/.test(value),
        upper: /[A-Z]/.test(value),
        number: /[0-9]/.test(value),
        symbol: /[!"#$%&'()*+,\-./:;<=>?@[\\\]^_`{|}~]/.test(value),
    };

    if (password.value !== confirm.value) {
        e.preventDefault();
        error.style.display = 'block';
        return;
    } else {
        error.style.display = 'none';
    }

    const allGood = Object.values(rules).every(Boolean);
    if (!allGood) {
        e.preventDefault();
    }
});

password.addEventListener('input', function () {
    const value = password.value;
    updateChecklist(value);
});

function updateChecklist(value) {
    const rules = {
        length: value.length >= 12,
        lower: /[a-z]/.test(value),
        upper: /[A-Z]/.test(value),
        number: /[0-9]/.test(value),
        symbol: /[!"#$%&'()*+,\-./:;<=>?@[\\\]^_`{|}~]/.test(value),
    };

    for (let rule in rules) {
        const item = document.getElementById('rule-' + rule);
        if (rules[rule]) {
            item.classList.add('ok');
        } else {
            item.classList.remove('ok');
        }
    }
}

function generatePassword(length = 16) {
    const lower = 'abcdefghijklmnopqrstuvwxyz';
    const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const numbers = '0123456789';
    const symbols = '!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~';

    const all = lower + upper + numbers + symbols;

    let password =
        lower[Math.floor(Math.random() * lower.length)] +
        upper[Math.floor(Math.random() * upper.length)] +
        numbers[Math.floor(Math.random() * numbers.length)] +
        symbols[Math.floor(Math.random() * symbols.length)];

    for (let i = password.length; i < length; i++) {
        password += all[Math.floor(Math.random() * all.length)];
    }

    password = password
        .split('')
        .sort(() => 0.5 - Math.random())
        .join('');
    return password;
}

document
    .getElementById('generate-password')
    .addEventListener('click', function (e) {
        e.preventDefault();
        const newPass = generatePassword();
        document.getElementById('password').value = newPass;
        document.getElementById('password_confirmation').value = newPass;
        updateChecklist(newPass);
    });

document.querySelectorAll('.toggle-password').forEach((btn) => {
    btn.addEventListener('click', function () {
        const input = this.previousElementSibling;
        const img = this.querySelector('img');

        if (input.type === 'password') {
            input.type = 'text';
            img.src = '../assets/img/nascondi.png';
            img.alt = 'Hide password';
        } else {
            input.type = 'password';
            img.src = '../assets/img/mostra.png';
            img.alt = 'Show password';
        }
    });
});
