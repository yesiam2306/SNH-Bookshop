/**
 * questo file viene usato all'interno di login.php solo per fare il nascondi password in maniera carina.
 */

const form = document.querySelector('.login-form');
const password = document.getElementById('password');

document.querySelectorAll('.toggle-password').forEach((btn) => {
    btn.addEventListener('click', function () {
        const input = this.previousElementSibling;
        const img = this.querySelector('img');

        if (input.type === 'password') {
            input.type = 'text';
            img.src = 'assets/img/nascondi.png';
            img.alt = 'Hide password';
        } else {
            input.type = 'password';
            img.src = 'assets/img/mostra.png';
            img.alt = 'Show password';
        }
    });
});
