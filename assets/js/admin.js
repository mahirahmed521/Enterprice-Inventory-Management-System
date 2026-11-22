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

// Live preview when a product image is chosen
const imageInput = document.getElementById('product_image');
const imagePreview = document.querySelector('[data-image-preview]');
if (imageInput && imagePreview) {
    imageInput.addEventListener('change', function () {
        if (imageInput.files && imageInput.files[0]) {
            imagePreview.src = URL.createObjectURL(imageInput.files[0]);
        }
    });
}
const pictureChoice = document.querySelector('[data-picture-choice]');
if (pictureChoice && imagePreview) {
    pictureChoice.addEventListener('change', function () {
        const picked = pictureChoice.options[pictureChoice.selectedIndex];
        if (picked && picked.getAttribute('data-src')) {
            imagePreview.src = picked.getAttribute('data-src');
        }
    });
}
