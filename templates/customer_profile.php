<section class="surface records-surface"><div class="section-heading"><h2>Shipping contact</h2></div>
<form class="module-form" method="post"><div class="fields">
<label class="field">Delivery name<input name="customer_name" maxlength="150" value="<?php echo htmlspecialchars($customer_name); ?>" required></label>
<label class="field">Phone<input name="phone" maxlength="30" value="<?php if (isset($phone)) { echo htmlspecialchars($phone); } ?>"></label>
<label class="field">Delivery address<textarea name="shipping_address"><?php if (isset($shipping_address)) { echo htmlspecialchars($shipping_address); } ?></textarea></label>
</div><div class="actions"><button class="btn btn-success">Save details</button></div></form></section>
