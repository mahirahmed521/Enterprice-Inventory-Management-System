const toggle = document.getElementById('toggle-password');
const password = document.getElementById('password');

toggle.addEventListener('click', function () {
    const show = password.type === 'password';
    password.type = show ? 'text' : 'password';
    toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    toggle.setAttribute('aria-pressed', String(show));
    toggle.innerHTML = show ? '<i class="bx bx-show" aria-hidden="true"></i>' : '<i class="bx bx-hide" aria-hidden="true"></i>';
});
