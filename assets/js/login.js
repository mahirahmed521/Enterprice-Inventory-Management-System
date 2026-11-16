const toggle = document.getElementById('toggle-password');
const password = document.getElementById('password');

toggle.addEventListener('click', function () {
    if (password.type === 'password') {
        password.type = 'text';
        toggle.setAttribute('aria-label', 'Hide password');
        toggle.setAttribute('aria-pressed', 'true');
        toggle.innerHTML = '<i class="bx bx-show" aria-hidden="true"></i>';
    } else {
        password.type = 'password';
        toggle.setAttribute('aria-label', 'Show password');
        toggle.setAttribute('aria-pressed', 'false');
        toggle.innerHTML = '<i class="bx bx-hide" aria-hidden="true"></i>';
    }
});
