<?php if ($editor) { ?>
<section class="surface records-surface"><div class="section-heading"><h2><?php if ($id > 0) {
        echo 'Edit record';
    } else {
        echo 'New record';
    } ?></h2></div>
<form method="post" class="module-form"><div class="fields">
    <label class="field">Company name
        <input name="company_name" type="text" value="<?php echo htmlspecialchars($company_name); ?>" required>
    </label>
    <label class="field">Contact person
        <input name="contact_person" type="text" value="<?php if (isset($contact_person)) { echo htmlspecialchars($contact_person); } ?>">
    </label>
    <label class="field">Email
        <input name="email" type="email" value="<?php if (isset($email)) { echo htmlspecialchars($email); } ?>">
    </label>
    <label class="field">Phone
        <input name="phone" type="text" value="<?php if (isset($phone)) { echo htmlspecialchars($phone); } ?>">
    </label>
    <label class="field">Tax id
        <input name="tax_id" type="text" value="<?php if (isset($tax_id)) { echo htmlspecialchars($tax_id); } ?>">
    </label>
    <label class="field">Address
        <textarea name="address"><?php if (isset($address)) { echo htmlspecialchars($address); } ?></textarea>
    </label>
    <label class="field">Status
        <select name="is_active"><option value="1" <?php if ($is_active == '1') {
            echo 'selected';
        } ?>>Active</option>
<option value="0" <?php if ($is_active == '0') {
        echo 'selected';
    } ?>>Inactive</option></select>
    </label>
</div><div class="actions"><button class="btn btn-success" name="action" value="save">Save</button><a href="suppliers.php">Cancel</a></div></form></section>
<?php } ?>
<section class="surface records-surface"><div class="section-heading"><h2>Suppliers (<?php echo $rows->num_rows; ?>)</h2><a class="btn btn-success" href="?editor=new">Add record</a></div>
<div class="table-responsive"><table class="table inventory-table records-table">
<thead><tr><th>Company name</th><th>Contact person</th><th>Email</th><th>Phone</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($rows)) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['company_name']); ?></td>
    <td><?php if (isset($row['contact_person'])) { echo htmlspecialchars($row['contact_person']); } ?></td>
    <td><?php if (isset($row['email'])) { echo htmlspecialchars($row['email']); } ?></td>
    <td><?php if (isset($row['phone'])) { echo htmlspecialchars($row['phone']); } ?></td>
    <td><span class="record-badge"><?php if ($row['is_active'] == 1) {
        echo 'Active';
    } else {
        echo 'Inactive';
    } ?></span></td>
    <td><a class="text-link" href="?id=<?php echo $row['supplier_id']; ?>">Edit</a> <form method="post" class="inline-form" action="?id=<?php echo $row['supplier_id']; ?>"><button class="btn btn-sm btn-link text-danger" name="action" value="delete">Delete</button></form></td>
</tr>
<?php } ?>
</tbody></table></div>
<?php if ($rows->num_rows == 0) { ?><p class="empty-state">No records to show.</p><?php } ?>
</section>
