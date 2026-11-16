<?php if ($editor) { ?>
<section class="surface records-surface"><div class="section-heading"><h2><?php if ($id > 0) {
        echo 'Edit record';
    } else {
        echo 'New record';
    } ?></h2></div>
<form method="post" class="module-form"><div class="fields">
    <label class="field">Warehouse code
        <input name="warehouse_code" type="text" value="<?php echo htmlspecialchars($warehouse_code); ?>" required>
    </label>
    <label class="field">Warehouse name
        <input name="warehouse_name" type="text" value="<?php echo htmlspecialchars($warehouse_name); ?>" required>
    </label>
    <label class="field">Location address
        <textarea name="location_address"><?php if (isset($location_address)) { echo htmlspecialchars($location_address); } ?></textarea>
    </label>
    <label class="field">Warehouse manager
        <select name="manager_id"><option value="">None</option><?php $choices = mysqli_query($conn, "SELECT user_id id,username label FROM users WHERE role IN ('ADMIN','MANAGER') AND is_active=1 ORDER BY username"); ?>
                <?php while ($choice = mysqli_fetch_assoc($choices)) { ?>
                    <option value="<?php echo $choice['id']; ?>" <?php if ($choice['id'] == $manager_id) {
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
</div><div class="actions"><button class="btn btn-success" name="action" value="save">Save</button><a href="warehouses.php">Cancel</a></div></form></section>
<?php } ?>
<section class="surface records-surface"><div class="section-heading"><h2>Warehouses (<?php echo $rows->num_rows; ?>)</h2><a class="btn btn-success" href="?editor=new">Add record</a></div>
<div class="table-responsive"><table class="table inventory-table records-table">
<thead><tr><th>Warehouse code</th><th>Warehouse name</th><th>Location address</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($rows)) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['warehouse_code']); ?></td>
    <td><?php echo htmlspecialchars($row['warehouse_name']); ?></td>
    <td><?php if (isset($row['location_address'])) { echo htmlspecialchars($row['location_address']); } ?></td>
    <td><span class="record-badge"><?php if ($row['is_active'] == 1) {
        echo 'Active';
    } else {
        echo 'Inactive';
    } ?></span></td>
    <td><a class="text-link" href="?id=<?php echo $row['warehouse_id']; ?>">Edit</a> <form method="post" class="inline-form" action="?id=<?php echo $row['warehouse_id']; ?>"><button class="btn btn-sm btn-link text-danger" name="action" value="delete">Delete</button></form></td>
</tr>
<?php } ?>
</tbody></table></div>
<?php if ($rows->num_rows == 0) { ?><p class="empty-state">No records to show.</p><?php } ?>
</section>
