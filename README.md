# Stockroom — Inventory Management System

Stockroom is a procedural PHP and MySQL application for receiving goods, managing warehouse stock and fulfilling customer orders. Open the [customer homepage](http://localhost/inventory_management_system/) or the [team sign-in page](http://localhost/inventory_management_system/admin/index.php) with Apache and MySQL running in Laragon.

## Accounts and responsibilities

| Role | Daily work |
| --- | --- |
| Admin | Maintain users, activate or disable accounts, manage records and view inventory valuation. |
| Manager | Manage products, categories, warehouses, customers and suppliers. Place purchase orders, confirm sales, adjust stock and review reorder alerts. |
| Staff | Receive deliveries, ship confirmed sales, adjust or transfer stock and update bin locations. |
| Customer | Search the catalog, choose products, place and track personal orders and update delivery details. |

Choose a role on the signup page to create an active local demo account. The imported demo logins are `admin@inventory.local`, `manager@inventory.local` and `staff@inventory.local`. Their sample password is `password`.

## Stock and order flow

1. The manager creates a purchase order, adds items and places the order. Staff can receive one line in multiple deliveries. Each receipt updates the warehouse balance.
2. A customer places a sales order. Adding a line reserves its units immediately. The manager confirms the order for shipment. Staff ship it to release the reservation and deduct the shipped units.
3. Cancelling a pending or confirmed sale releases its reserved units. A transfer moves the same quantity out of one warehouse and into another. Posted movements stay in the history. A correcting adjustment records a new entry.
4. The reorder report compares available stock with the product’s reorder point. The valuation report multiplies on-hand units by their current product cost. Order totals use the stored line quantities and unit prices.

## Files and database

- `index.html` is the public homepage. `catalog.php` reads products and availability from MySQL.
- `admin/` contains login, signup, the four role dashboards, record forms, order processing, warehouse operations and reports.
- `includes/` contains authentication and shared page layouts. The live forms and tables are in `templates/`.
- `config/db.php` connects to the local `intermediate_inventory` database using MySQLi.
- On a **fresh installation**, create an empty database named `intermediate_inventory`, import `dabatases/intermediate_inventory.sql` once. It includes the sample records, customer accounts, triggers and views.
- The schema has 12 tables. The customer-account update extends the role column and links a user to a customer. The application uses four triggers for purchase totals, sales reservations, negative-stock checks and warehouse balances, plus two reporting views.

The storefront uses a locally bundled [warehouse photograph from Unsplash](https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d).

<details>
<summary>Earlier implementation notes (historical)</summary>

# Stockroom - Inventory Management System

A small PHP/MySQL project, organized like the bus-ticket reference project.

## Open the project

Start Apache and MySQL in Laragon, then open:

http://localhost/inventory_management_system/

Database connection: `config/db.php` (127.0.0.1, port 3306, root, blank password, intermediate_inventory).
The existing `dabatases/intermediate_inventory.sql` is kept in its original location. The application uses the database you already imported; it does not import or reset it.

Existing imported accounts, verified locally:

| Role | Email | Password |
| --- | --- | --- |
| Admin | admin@inventory.local | password |
| Manager | manager@inventory.local | password |
| Staff | staff@inventory.local | password |

These three original accounts retain their existing passwords. Three additional accounts were repaired earlier by converting plain-text password storage to bcrypt, without changing the passwords themselves. New database pages only display records; they do not update the database.

## Files

```text
inventory_management_system/
  index.php                 Redirects to login or dashboard
  admin/
    index.php               Login form and PHP login processing
    dashboard.php           Dashboard with database counts and products
    logout.php              Clears the session and returns to login
    products.php            Product catalog and prices
    categories.php          Categories and parent categories
    suppliers.php           Supplier directory
    customers.php           Customer directory
    warehouses.php          Warehouses and managers
    warehouse_stock.php     Stock balances by warehouse
    purchase_orders.php     Purchase orders and expandable line items
    sales_orders.php        Sales orders and expandable line items
    stock_movements.php     Receipts, shipments and adjustments
    inventory_valuation.php Inventory valuation database view
    reorder_alerts.php      Reorder-alert database view
    users.php               Account information, excluding passwords
  config/
    db.php                  MySQL connection
  includes/
    auth.php                Checks the login session
    admin_css.php           Shared stylesheet links
    admin_script.php        Shared script links
    sidebar.php             Dashboard navigation
    topbar.php              Signed-in user information
    footer.php              Dashboard footer
    dashboard_data.php      Read-only dashboard queries and totals
    record_pages.php        Queries and column headings for each page
    records_page.php        Shared page layout and table rendering
  assets/
    css/                    Bootstrap, icons, dashboard and login styles
    js/                     Bootstrap, menu and password toggle scripts
    fonts/                  Local icon fonts
    images/                 Login illustration
  dabatases/
    intermediate_inventory.sql
```

## How login works

1. The form posts an email and password to `admin/index.php`.
2. A MySQLi query finds that email in `users`.
3. `password_verify()` checks the database's existing bcrypt password hash.
4. An active account's ID, username, email and role are saved in the PHP session.
5. PHP redirects to `admin/dashboard.php`. `includes/auth.php` sends visitors without a login session back to login.
6. `admin/logout.php` clears and destroys the session, then redirects to login with a success message.

All three existing roles (ADMIN, MANAGER, STAFF) use the same read-only dashboard at this stage. Role-specific pages, registration, password resets, and inventory editing are not implemented.

The reference bus project uses SHA-1 and a different users table. Copying its password check directly would fail with this database. Login processing here also runs before HTML so redirects work without relying on output buffering.

The login code uses ordinary `if`/`elseif` blocks, `mysqli_query()`, `mysqli_num_rows()` and `mysqli_fetch_assoc()`. `mysqli_real_escape_string()` handles quotes in the submitted email before using it in the query. `password_verify()` checks the entered password against its stored hash; it cannot be replaced by a plain-text equality check. Explicit type casts and null-coalescing expressions were removed from the login handler to keep its flow easier to follow.

## Dashboard and database pages

The overview includes warehouse selection, 7/30-day stock-movement charts, stock-health counts, product search, stock-status filters, product details and recent activity. Refresh reloads the database figures; it is not a background live feed. The available quantity is on-hand minus reserved stock. Inventory cost is on-hand quantity multiplied by unit cost. Prices are displayed in the database's existing units, without assuming a currency.

The dashboard covers active products, with the whole active catalog considered at each selected warehouse. Reports use the existing database views across all warehouses. The movement chart includes positive and negative transfers and adjustments as well as receipts and shipments. Dates use Asia/Dhaka.

All 12 database pages have search and pagination; relevant pages also have status, role, category or warehouse filters. Purchase and sales orders support expandable line items. Search on order pages also matches the products in those items. These pages use basic PHP includes and procedural MySQLi; there is no framework or build step.

The new appearance is in `assets/css/dashboard.css`. Menu, search, filter, dialog, pagination and counter interactions are in `assets/js/admin.js`. CSS/JS version parameters change when those files change to prevent stale browser assets.

## Template source

Adapted from the locally supplied Dashtreme Bootstrap 5 template by codervent: sidebar/card layout, cover-login arrangement, Bootstrap CSS/JS, icon CSS/fonts and login illustration. The refreshed workspace adds forest-green and sage styling in `assets/css/dashboard.css`. Original vendor headers are retained. No template build tools or PHP framework are required.

## Verification

- All 26 PHP files passed PHP 8.3 syntax checks during this update; modified PHP files were checked again after subsequent changes.
- Dashboard and all 12 database pages returned HTTP 200 without PHP warnings or errors.
- All three existing accounts passed login, dashboard, logout and logged-out redirect checks over HTTP.
- Incorrect passwords and empty input were rejected.
- Referenced styles, scripts, illustration and the Boxicons font returned HTTP 200.
- Browser checks confirmed desktop and mobile layouts, the mobile menu, account navigation, login, logout and the dashboard redirect after logout. No browser JavaScript warnings or errors were reported during the dashboard check.
- Live counts at verification: 3 products, 3 categories, 2 suppliers and 2 warehouses. Dashboard queries are read-only.
- Browser checks covered product search, missing search results, low-stock filtering, product details, warehouse/date-range changes, role filtering, filter resets and purchase line items.
- The current live sales-order and sales-order-item tables were empty, so the sales page's empty state was verified. No sample sales were inserted.
- Page-size controls are available; multi-page navigation was not exercised against the live data because each current view has fewer than 10 records.
- At 390px width, the dashboard no longer overflows horizontally; wide data tables scroll within their panels. The mobile navigation overlay was checked after correcting its click area.

During testing, the database connection initially failed because MySQL was stopped. The existing MySQL and Apache services were started. A variable-name collision between the database password and form password was found and fixed by prefixing connection variables with `db`; all login tests then passed.

The dashboard update also fixed narrow table wrapping, mobile overflow from an absolutely positioned hidden heading, and browser caching of old styles. The simplified login passed tests for the three original roles and for wrong, empty, quoted and malformed form input.

</details>
