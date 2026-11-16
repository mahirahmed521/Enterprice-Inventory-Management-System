<?php if ($editor) { ?>
<section class="surface records-surface"><div class="section-heading"><h2><?php if ($id > 0) {
        echo 'Edit record';
    } else {
        echo 'New record';
    } ?></h2></div>
<form method="post" class="module-form"><div class="fields">
    <label class="field">Name
        <input name="name" type="text" value="<?php echo htmlspecialchars($name); ?>" required>
    </label>
    <label class="field">Parent category
        <select name="parent_id"><option value="">None</option><?php $choices = mysqli_query($conn, "SELECT category_id id,name label FROM categories ORDER BY name"); ?>
                <?php while ($choice = mysqli_fetch_assoc($choices)) { ?>
                    <option value="<?php echo $choice['id']; ?>" <?php if ($choice['id'] == $parent_id) {
                        echo 'selected';
                    } ?>><?php echo htmlspecialchars($choice['label']); ?></option>
                <?php } ?></select>
    </label>
    <label class="field">Description
        <textarea name="description"><?php if (isset($description)) { echo htmlspecialchars($description); } ?></textarea>
    </label>
</div><div class="actions"><button class="btn btn-success" name="action" value="save">Save</button><a href="categories.php">Cancel</a></div></form></section>
<?php } ?>
<section class="surface records-surface"><div class="section-heading"><h2>Categories (<?php echo $rows->num_rows; ?>)</h2><a class="btn btn-success" href="?editor=new">Add record</a></div>
<div class="table-responsive"><table class="table inventory-table records-table">
<thead><tr><th>Name</th><th>Description</th><th>Actions</th></tr></thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($rows)) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['name']); ?></td>
    <td><?php if (isset($row['description'])) { echo htmlspecialchars($row['description']); } ?></td>
    <td><a class="text-link" href="?id=<?php echo $row['category_id']; ?>">Edit</a> <form method="post" class="inline-form" action="?id=<?php echo $row['category_id']; ?>"><button class="btn btn-sm btn-link text-danger" name="action" value="delete">Delete</button></form></td>
</tr>
<?php } ?>
</tbody></table></div>
<?php if ($rows->num_rows == 0) { ?><p class="empty-state">No records to show.</p><?php } ?>
</section>
