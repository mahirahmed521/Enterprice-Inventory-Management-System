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
document.querySelectorAll('[data-auto-submit]').forEach(function (select) {
    select.addEventListener('change', function () { select.form.requestSubmit(); });
});

// Dashboard product search and stock-status filters.
const productSearch = document.getElementById('product-search');
if (productSearch) {
    const status = document.getElementById('product-status');
    const rows = Array.from(document.querySelectorAll('[data-product-row]'));
    function filterProducts() {
        const query = productSearch.value.trim().toLowerCase();
        let count = 0;
        rows.forEach(function (row) {
            const matches = row.dataset.search.includes(query) && (status.value === 'all' || row.dataset.status === status.value);
            row.hidden = !matches;
            if (matches) count++;
        });
        document.getElementById('no-products').hidden = count > 0;
        document.getElementById('product-count').textContent = count + ' of ' + rows.length + ' products';
    }
    productSearch.addEventListener('input', filterProducts);
    status.addEventListener('change', filterProducts);
    document.getElementById('reset-products').addEventListener('click', function () {
        productSearch.value = '';
        status.value = 'all';
        filterProducts();
    });
    document.querySelectorAll('[data-health-filter]').forEach(function (button) {
        button.addEventListener('click', function () {
            status.value = button.dataset.healthFilter;
            productSearch.value = '';
            filterProducts();
            document.getElementById('products').scrollIntoView({behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth', block: 'start'});
            status.focus({preventScroll:true});
        });
    });

    const products = JSON.parse(document.getElementById('product-data').textContent);
    const dialog = document.getElementById('product-dialog');
    const detailFields = document.getElementById('detail-fields');
    document.querySelectorAll('[data-product-id]').forEach(function (button) {
        button.addEventListener('click', function () {
            const product = products.find(function (item) { return String(item.product_id) === button.dataset.productId; });
            document.getElementById('detail-name').textContent = product.product_name;
            document.getElementById('detail-sku').textContent = product.sku + ' · ' + product.category_name;
            detailFields.replaceChildren();
            const fields = {'On hand':product.on_hand, 'Reserved':product.reserved, 'Available':product.available,
                'Reorder point':product.reorder_point, 'Unit':product.unit_of_measure,
                'Cost price':Number(product.cost_price).toFixed(2), 'Selling price':Number(product.selling_price).toFixed(2), 'Supplier':product.supplier_name};
            Object.entries(fields).forEach(function ([label,value]) {
                const term = document.createElement('dt');
                const description = document.createElement('dd');
                term.textContent = label;
                description.textContent = value;
                detailFields.append(term, description);
            });
            dialog.showModal();
        });
    });
    document.getElementById('close-product').addEventListener('click', function () { dialog.close(); });
    dialog.addEventListener('click', function (event) {
        if (event.target === dialog) {
            const bounds = dialog.getBoundingClientRect();
            if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close();
        }
    });
}

// Shared search, filtering and pagination for the database pages.
const recordSearch = document.getElementById('record-search');
if (recordSearch) {
    const rows = Array.from(document.querySelectorAll('[data-record-row]'));
    const filter = document.getElementById('record-filter');
    const size = document.getElementById('page-size');
    const previous = document.getElementById('previous-page');
    const next = document.getElementById('next-page');
    let page = 1;

    function displayRecords() {
        const query = recordSearch.value.trim().toLowerCase();
        const matches = rows.filter(function (row) {
            return row.dataset.search.includes(query) && (!filter || !filter.value || row.dataset.filter === filter.value);
        });
        const pageSize = Number(size.value);
        const pages = Math.max(1, Math.ceil(matches.length / pageSize));
        page = Math.min(page, pages);
        const start = (page - 1) * pageSize;
        rows.forEach(function (row) { row.hidden = true; });
        matches.slice(start, start + pageSize).forEach(function (row) { row.hidden = false; });
        document.getElementById('no-records').hidden = matches.length > 0;
        document.getElementById('record-count').textContent = matches.length ? 'Showing ' + (start + 1) + '–' + Math.min(start + pageSize, matches.length) + ' of ' + matches.length + ' records' : '0 records';
        document.getElementById('page-number').textContent = page + ' / ' + pages;
        previous.disabled = page === 1;
        next.disabled = page === pages;
    }
    function resetPage() { page = 1; displayRecords(); }
    recordSearch.addEventListener('input', resetPage);
    if (filter) filter.addEventListener('change', resetPage);
    size.addEventListener('change', resetPage);
    previous.addEventListener('click', function () { page--; displayRecords(); });
    next.addEventListener('click', function () { page++; displayRecords(); });
    document.getElementById('reset-records').addEventListener('click', function () {
        recordSearch.value = '';
        if (filter) filter.value = '';
        resetPage();
    });
    displayRecords();
}

if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const started = performance.now();
    const counters = document.querySelectorAll('[data-counter]');
    function animateCounters(now) {
        const progress = Math.min(1, (now - started) / 650);
        counters.forEach(function (counter) {
            counter.textContent = Math.round(Number(counter.dataset.counter) * (1 - Math.pow(1 - progress, 3))).toLocaleString('en-US');
        });
        if (progress < 1 && counters.length) requestAnimationFrame(animateCounters);
    }
    if (counters.length) requestAnimationFrame(animateCounters);
}
