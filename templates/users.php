<?php if ($editor) { ?>
<section class="surface records-surface"><div class="section-heading"><h2><?php if ($id > 0) {
        echo 'Edit record';
    } else {
        echo 'New record';
    } ?></h2></div>
<form method="post" class="module-form"><div class="fields">
    <label class="field">Username
        <input name="username" type="text" value="<?php if (isset($username)) { echo htmlspecialchars($username); } ?>" required>
    </label>
    <label class="field">Email
        <input name="email" type="email" value="<?php if (isset($email)) { echo htmlspecialchars($email); } ?>" required>
    </label>
    <label class="field">Role
        <select name="role"><option value="ADMIN" <?php if ($role == 'ADMIN') {
            echo 'selected';
        } ?>>ADMIN</option>
<option value="MANAGER" <?php if ($role == 'MANAGER') {
        echo 'selected';
    } ?>>MANAGER</option>
<option value="STAFF" <?php if ($role == 'STAFF') {
        echo 'selected';
    } ?>>STAFF</option>
<option value="CUSTOMER" <?php if ($role == 'CUSTOMER') {
        echo 'selected';
    } ?>>CUSTOMER</option></select>
    </label>
    <label class="field">Customer record
        <select name="customer_id"><option value="">None</option><?php $choices = mysqli_query($conn, "SELECT customer_id id,customer_name label FROM customers ORDER BY customer_name"); ?>
                <?php while ($choice = mysqli_fetch_assoc($choices)) { ?>
                    <option value="<?php echo $choice['id']; ?>" <?php if ($choice['id'] == $customer_id) {
                        echo 'selected';
                    } ?>><?php echo htmlspecialchars($choice['label']); ?></option>
                <?php } ?></select>
    </label>
    <label class="field">Status
        <select name="is_active"><option value="1" <?php if ($is_active == '1') {
            echo 'selected';
        } ?>>Active</option>
<option value="0" <?php if ($is_active == '0') {
        echo 'selected';
    } ?>>Inactive</option></select>
    </label>
    <label class="field">Password (leave blank to keep existing)
        <input name="password" type="password" autocomplete="new-password">
    </label>
</div><div class="actions"><button class="btn btn-success" name="action" value="save">Save</button><a href="users.php">Cancel</a></div></form></section>
<?php } ?>
<section class="surface records-surface"><div class="section-heading"><h2>Users (<?php echo $rows->num_rows; ?>)</h2><a class="btn btn-success" href="?editor=new">Add record</a></div>
<div class="table-responsive"><table class="table inventory-table records-table">
<thead><tr><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($rows)) { ?>
<tr>
    <td><?php if (isset($row['username'])) { echo htmlspecialchars($row['username']); } ?></td>
    <td><?php if (isset($row['email'])) { echo htmlspecialchars($row['email']); } ?></td>
    <td><?php echo htmlspecialchars($row['role']); ?></td>
    <td><span class="record-badge"><?php if ($row['is_active'] == 1) {
        echo 'Active';
    } else {
        echo 'Inactive';
    } ?></span></td>
    <td><a class="text-link" href="?id=<?php echo $row['user_id']; ?>">Edit</a> <form method="post" class="inline-form" action="?id=<?php echo $row['user_id']; ?>"><button class="btn btn-sm btn-link text-danger" name="action" value="delete">Delete</button></form></td>
</tr>
<?php } ?>
</tbody></table></div>
<?php if ($rows->num_rows == 0) { ?><p class="empty-state">No records to show.</p><?php } ?>
</section>
