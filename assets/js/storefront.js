// Stockroom storefront: flash sale timer, quantity stepper, payment toggle, image preview, alert close
(function () {
    // Flash sale countdown to the end of today (browser time)
    var countdown = document.querySelector('[data-countdown]');
    if (countdown) {
        var hoursBox = countdown.querySelector('[data-hours]');
        var minutesBox = countdown.querySelector('[data-minutes]');
        var secondsBox = countdown.querySelector('[data-seconds]');
        var endOfDay = function () {
            var now = new Date();
            return new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 0, 0, 0).getTime();
        };
        var saleEnd = endOfDay();
        var tick = function () {
            var remaining = saleEnd - Date.now();
            if (remaining < 0) {
                saleEnd = endOfDay();
                remaining = saleEnd - Date.now();
            }
            var h = Math.floor(remaining / 3600000);
            var m = Math.floor((remaining % 3600000) / 60000);
            var s = Math.floor((remaining % 60000) / 1000);
            hoursBox.textContent = String(h).padStart(2, '0');
            minutesBox.textContent = String(m).padStart(2, '0');
            secondsBox.textContent = String(s).padStart(2, '0');
        };
        tick();
        setInterval(tick, 1000);
    }

    // Quantity [-] [+] stepper, capped at the available stock
    var steppers = document.querySelectorAll('[data-quantity-stepper]');
    Array.prototype.forEach.call(steppers, function (stepper) {
        var input = stepper.querySelector('input');
        var minus = stepper.querySelector('[data-minus]');
        var plus = stepper.querySelector('[data-plus]');
        var maximum = parseInt(stepper.getAttribute('data-max'), 10);
        if (isNaN(maximum) || maximum < 1) {
            maximum = 1;
        }
        // Cart steppers save the new quantity straight away
        var autoSubmit = function () {
            var form = stepper.closest('form');
            if (form && form.hasAttribute('data-autosubmit')) {
                form.submit();
            }
        };
        minus.addEventListener('click', function () {
            var value = parseInt(input.value, 10) - 1;
            if (isNaN(value) || value < 1) {
                value = 1;
            }
            input.value = value;
            autoSubmit();
        });
        plus.addEventListener('click', function () {
            var value = parseInt(input.value, 10) + 1;
            if (isNaN(value) || value > maximum) {
                value = maximum;
            }
            input.value = value;
            autoSubmit();
        });
    });

    // Transaction ID box: shown for BKASH, NAGAD, CARD and BANK, hidden for COD
    var payments = document.querySelectorAll('input[name="payment_method"]');
    var transaction = document.querySelector('[data-transaction-field]');
    if (transaction && payments.length > 0) {
        var updateTransaction = function () {
            var selected = 'COD';
            Array.prototype.forEach.call(payments, function (item) {
                if (item.checked) {
                    selected = item.value;
                }
            });
            transaction.hidden = (selected === 'COD');
        };
        Array.prototype.forEach.call(payments, function (item) {
            item.addEventListener('change', updateTransaction);
        });
        updateTransaction();
    }

    // Live image preview for file inputs that have a matching preview image
    var imageInput = document.querySelector('#product_image');
    var imagePreview = document.querySelector('[data-image-preview]');
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function () {
            if (imageInput.files && imageInput.files[0]) {
                imagePreview.src = URL.createObjectURL(imageInput.files[0]);
            }
        });
    }

    // Close buttons on the cart confirmation alerts
    var closers = document.querySelectorAll('[data-dismiss-alert]');
    Array.prototype.forEach.call(closers, function (button) {
        button.addEventListener('click', function () {
            var box = button.closest('.alert');
            if (box) {
                box.remove();
            }
        });
    });
})();
