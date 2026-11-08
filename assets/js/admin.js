const menuButton = document.getElementById('menu-toggle');
const overlay = document.getElementById('sidebar-overlay');

function closeMenu() {
    document.body.classList.remove('sidebar-open');
    menuButton.setAttribute('aria-expanded', 'false');
    overlay.hidden = true;
}
menuButton.addEventListener('click', function () {
    const open = document.body.classList.toggle('sidebar-open');
    menuButton.setAttribute('aria-expanded', String(open));
    overlay.hidden = !open;
});
overlay.addEventListener('click', closeMenu);
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closeMenu();
});
