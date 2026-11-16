<?php if ($editor) { ?>
<section class="surface records-surface"><div class="section-heading"><h2><?php if ($id > 0) {
        echo 'Edit record';
    } else {
        echo 'New record';
    } ?></h2></div>
<form method="post" class="module-form"><div class="fields">
    <label class="field">Customer name
        <input name="customer_name" type="text" value="<?php echo htmlspecialchars($customer_name); ?>" required>
    </label>
    <label class="field">Email
        <input name="email" type="email" value="<?php if (isset($email)) { echo htmlspecialchars($email); } ?>">
    </label>
    <label class="field">Phone
        <input name="phone" type="text" value="<?php if (isset($phone)) { echo htmlspecialchars($phone); } ?>">
    </label>
    <label class="field">Shipping address
        <textarea name="shipping_address"><?php if (isset($shipping_address)) { echo htmlspecialchars($shipping_address); } ?></textarea>
    </label>
</div><div class="actions"><button class="btn btn-success" name="action" value="save">Save</button><a href="customers.php">Cancel</a></div></form></section>
<?php } ?>
<section class="surface records-surface"><div class="section-heading"><h2>Customers (<?php echo $rows->num_rows; ?>)</h2><a class="btn btn-success" href="?editor=new">Add record</a></div>
<div class="table-responsive"><table class="table inventory-table records-table">
<thead><tr><th>Customer name</th><th>Email</th><th>Phone</th><th>Shipping address</th><th>Actions</th></tr></thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($rows)) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
    <td><?php if (isset($row['email'])) { echo htmlspecialchars($row['email']); } ?></td>
    <td><?php if (isset($row['phone'])) { echo htmlspecialchars($row['phone']); } ?></td>
    <td><?php if (isset($row['shipping_address'])) { echo htmlspecialchars($row['shipping_address']); } ?></td>
    <td><a class="text-link" href="?id=<?php echo $row['customer_id']; ?>">Edit</a> <form method="post" class="inline-form" action="?id=<?php echo $row['customer_id']; ?>"><button class="btn btn-sm btn-link text-danger" name="action" value="delete">Delete</button></form></td>
</tr>
<?php } ?>
</tbody></table></div>
<?php if ($rows->num_rows == 0) { ?><p class="empty-state">No records to show.</p><?php } ?>
</section>
