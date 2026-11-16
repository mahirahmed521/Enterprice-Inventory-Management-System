const menuButton = document.getElementById('menu-toggle');
const overlay = document.getElementById('sidebar-overlay');

function closeMenu() {
    document.body.classList.remove('sidebar-open');
    menuButton.setAttribute('aria-expanded', 'false');
    overlay.hidden = true;
}

menuButton.addEventListener('click', function () {
    if (document.body.classList.contains('sidebar-open')) {
        closeMenu();
    } else {
        document.body.classList.add('sidebar-open');
        menuButton.setAttribute('aria-expanded', 'true');
        overlay.hidden = false;
    }
});

overlay.addEventListener('click', closeMenu);
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeMenu();
    }
});
