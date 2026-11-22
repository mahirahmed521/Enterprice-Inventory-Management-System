<div class="page-heading">
    <div><span class="eyebrow">Inventory catalog</span><h1>Products<span class="heading-dot">.</span></h1></div>
    <?php if ($can_edit) { ?><a class="btn btn-success" href="products.php?editor=new">Add product</a><?php } ?>
</div>
<?php if ($message) { ?><p class="alert alert-success" role="status"><?php echo htmlspecialchars($message); ?></p><?php } ?>
<?php if ($error) { ?><p class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></p><?php } ?>
<?php if ($show_editor) { ?>
<section class="surface records-surface mb-4">
    <div class="section-heading"><h2><?php if ($id > 0) {
        echo 'Edit product';
    } else {
        echo 'Add product';
    } ?></h2></div>
    <form method="post" action="products.php?id=<?php echo $id; ?>" enctype="multipart/form-data" class="px-4 pb-4">
        <input type="hidden" name="existing_product_image" value="<?php echo htmlspecialchars($product_image); ?>">
        <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-4">
                <label class="form-label" for="product_image">Product image</label>
                <input class="form-control" id="product_image" name="product_image" type="file" accept="image/*">
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <img src="../<?php echo htmlspecialchars($product_image); ?>" data-image-preview alt="Product preview" style="width:120px;height:120px;object-fit:cover" onerror="this.src='../uploads/products/default.png'">
            </div>
        </div>
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-xl-4">
                <label class="form-label" for="sku">SKU</label>
                <input class="form-control" id="sku" name="sku" type="text" value="<?php echo htmlspecialchars($sku); ?>" maxlength="50" required>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <label class="form-label" for="product_name">Product name</label>
                <input class="form-control" id="product_name" name="product_name" type="text" value="<?php echo htmlspecialchars($product_name); ?>" maxlength="200" required>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <label class="form-label" for="barcode">Barcode</label>
                <input class="form-control" id="barcode" name="barcode" type="text" value="<?php if (isset($barcode)) { echo htmlspecialchars($barcode); } ?>" maxlength="100">
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <label class="form-label" for="brand">Brand</label>
                <input class="form-control" id="brand" name="brand" type="text" value="<?php echo htmlspecialchars($brand); ?>" maxlength="100">
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <label class="form-label" for="warranty_period">Warranty period</label>
                <input class="form-control" id="warranty_period" name="warranty_period" type="text" value="<?php echo htmlspecialchars($warranty_period); ?>" maxlength="100">
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <label class="form-label" for="is_featured">Featured in Flash Sale</label>
                <select class="form-select" id="is_featured" name="is_featured">
                    <option value="0" <?php if ($is_featured == 0) { echo 'selected'; } ?>>Regular</option>
                    <option value="1" <?php if ($is_featured == 1) { echo 'selected'; } ?>>Featured</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="specifications">Specifications</label>
                <textarea class="form-control" id="specifications" name="specifications" rows="4"><?php echo htmlspecialchars($specifications); ?></textarea>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <label class="form-label" for="category_id">Category</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">None</option>
                    <?php while ($choice = mysqli_fetch_assoc($categories)) { ?>
                        <option value="<?php echo $choice['category_id']; ?>" <?php if ($category_id == $choice['category_id']) {
                            echo 'selected';
                        } ?>><?php echo htmlspecialchars($choice['name']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <label class="form-label" for="preferred_supplier_id">Preferred supplier</label>
                <select class="form-select" id="preferred_supplier_id" name="preferred_supplier_id">
                    <option value="">None</option>
                    <?php while ($choice = mysqli_fetch_assoc($suppliers)) { ?>
                        <option value="<?php echo $choice['supplier_id']; ?>" <?php if ($preferred_supplier_id == $choice['supplier_id']) {
                            echo 'selected';
                        } ?>><?php echo htmlspecialchars($choice['company_name']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <label class="form-label" for="unit_of_measure">Unit of measure</label>
                <input class="form-control" id="unit_of_measure" name="unit_of_measure" type="text" value="<?php echo htmlspecialchars($unit_of_measure); ?>" maxlength="20" required>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <label class="form-label" for="cost_price">Cost price</label>
                <input class="form-control" id="cost_price" name="cost_price" type="number" value="<?php echo htmlspecialchars($cost_price); ?>" min="0" step="0.01" required>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <label class="form-label" for="selling_price">Selling price</label>
                <input class="form-control" id="selling_price" name="selling_price" type="number" value="<?php echo htmlspecialchars($selling_price); ?>" min="0" step="0.01" required>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <label class="form-label" for="tax_rate">Tax rate (%)</label>
                <input class="form-control" id="tax_rate" name="tax_rate" type="number" value="<?php echo htmlspecialchars($tax_rate); ?>" min="0" step="0.01" required>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <label class="form-label" for="reorder_point">Reorder point</label>
                <input class="form-control" id="reorder_point" name="reorder_point" type="number" value="<?php echo htmlspecialchars($reorder_point); ?>" min="0" step="1" required>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <label class="form-label" for="reorder_quantity">Reorder quantity</label>
                <input class="form-control" id="reorder_quantity" name="reorder_quantity" type="number" value="<?php echo htmlspecialchars($reorder_quantity); ?>" min="0" step="1" required>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <label class="form-label" for="is_active">Status</label>
                <select class="form-select" id="is_active" name="is_active">
                    <option value="1" <?php if ($is_active == 1) {
                        echo 'selected';
                    } ?>>Active</option>
                    <option value="0" <?php if ($is_active == 0) {
                        echo 'selected';
                    } ?>>Inactive</option>
                </select>
            </div>
        </div>
        <div class="d-flex gap-3 align-items-center mt-4">
            <button class="btn btn-success" name="action" value="save">Save product</button>
            <a class="text-link" href="products.php">Cancel</a>
        </div>
    </form>
</section>
<?php } ?>
<div class="records-banner">
    <span class="records-banner-icon"><i class="bx bx-package" aria-hidden="true"></i></span>
    <div><strong><?php echo mysqli_num_rows($products); ?></strong><span>Products in this view</span></div>
</div>
<section class="surface records-surface">
    <div class="section-heading"><div><h2>Product catalog</h2><p>Available units exclude stock reserved for orders.</p></div></div>
    <form method="get" class="table-toolbar">
        <label class="search-box"><i class="bx bx-search" aria-hidden="true"></i><input type="search" aria-label="Search by name or SKU" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name or SKU"></label>
        <select class="form-select" aria-label="Product status" name="status">
            <option value="">All statuses</option>
            <option value="1" <?php if ($status === '1') {
                echo 'selected';
            } ?>>Active</option>
            <option value="0" <?php if ($status === '0') {
                echo 'selected';
            } ?>>Inactive</option>
        </select>
        <button class="btn btn-sm btn-success">Apply</button><a class="text-link" href="products.php">Reset</a>
    </form>
    <div class="table-responsive">
        <table class="table inventory-table records-table">
            <thead><tr><th>Image</th><th>Product / SKU</th><th>Brand</th><th>Category</th><th>Available</th>
                <?php if ($can_edit) { ?><th>Cost price</th><?php } ?>
                <th>Selling price</th><th>Status</th><?php if ($can_edit) { ?><th>Actions</th><?php } ?>
            </tr></thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($products)) { ?>
                <tr>
                    <?php $row_image = $row['product_image']; if ($row_image == '') { $row_image = 'uploads/products/default.png'; } ?>
                    <td><img src="../<?php echo htmlspecialchars($row_image); ?>" alt="" style="width:64px;height:64px;object-fit:cover" onerror="this.src='../uploads/products/default.png'"></td>
                    <td><strong><?php echo htmlspecialchars($row['product_name']); ?></strong><br><small><?php echo htmlspecialchars($row['sku']); ?></small></td>
                    <td><?php echo htmlspecialchars($row['brand']); ?></td>
                    <td><?php if ($row['category'] == null) {
                        echo 'Uncategorized';
                    } else {
                        echo htmlspecialchars($row['category']);
                    } ?></td>
                    <td><span class="stock-badge <?php if ($row['available_to_sell'] <= 0) {
                        echo 'out';
                    } elseif ($row['available_to_sell'] <= $row['reorder_point']) {
                        echo 'low';
                    } ?>">
                        <?php echo $row['available_to_sell']; ?> <?php echo htmlspecialchars($row['unit_of_measure']); ?>
                    </span></td>
                    <?php if ($can_edit) { ?><td><?php echo $row['cost_price']; ?></td><?php } ?>
                    <td><?php echo $row['selling_price']; ?></td>
                    <td><?php if ($row['is_active'] == 1) { ?><span class="record-badge">Active</span><?php } else { ?><span class="record-badge muted-badge">Inactive</span><?php } ?></td>
                    <?php if ($can_edit) { ?>
                        <td><a class="text-link" href="products.php?id=<?php echo $row['product_id']; ?>">Edit</a>
                            <form class="d-inline" method="post" action="products.php?id=<?php echo $row['product_id']; ?>">
                                <button class="btn btn-link btn-sm text-danger" name="action" value="delete">Delete</button>
                            </form>
                        </td>
                    <?php } ?>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php if (mysqli_num_rows($products) == 0) { ?><p class="px-4 pb-3">No products match this view.</p><?php } ?>
</section>
