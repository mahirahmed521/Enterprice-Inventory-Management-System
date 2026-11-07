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

These credentials already matched the imported hashes; no passwords or database rows were changed.

## Files

```text
inventory_management_system/
  index.php                 Redirects to login or dashboard
  admin/
    index.php               Login form and PHP login processing
    dashboard.php           Dashboard with database counts and products
    logout.php              Clears the session and returns to login
  config/
    db.php                  MySQL connection
  includes/
    auth.php                Checks the login session
    admin_css.php           Shared stylesheet links
    admin_script.php        Shared script links
    sidebar.php             Dashboard navigation
    topbar.php              Signed-in user information
    footer.php              Dashboard footer
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

## Template source

Adapted from the locally supplied Dashtreme Bootstrap 5 template by codervent: sidebar/card layout, cover-login arrangement, Bootstrap CSS/JS, icon CSS/fonts and login illustration. Custom navy/teal styling is in `assets/css/admin.css` and `assets/css/login.css`. Original vendor headers are retained. No template build tools or PHP framework are required.

## Verification

- All 11 PHP files passed PHP 8.3 syntax checks.
- All three existing accounts passed login, dashboard, logout and logged-out redirect checks over HTTP.
- Incorrect passwords and empty input were rejected.
- Referenced styles, scripts, illustration and the Boxicons font returned HTTP 200.
- Browser checks confirmed desktop and mobile layouts, the mobile menu, account navigation, login, logout and the dashboard redirect after logout. No browser JavaScript warnings or errors were reported during the dashboard check.
- Live counts at verification: 3 products, 3 categories, 2 suppliers and 2 warehouses. Dashboard queries are read-only.

During testing, the database connection initially failed because MySQL was stopped. The existing MySQL and Apache services were started. A variable-name collision between the database password and form password was found and fixed by prefixing connection variables with `db`; all login tests then passed.
