<?php
require_once __DIR__ . '/../includes/check_fields.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
$pageTitle = 'Product reviews';
$section = 'Customer feedback';
$error = '';
$action = '';
$review_id = 0;
if (isset($_POST['action'])) { $action = $_POST['action']; }
if (isset($_POST['review_id'])) { $review_id = (int) $_POST['review_id']; }
if ($supervisor == false) { exit('Access denied.'); }
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'delete') {
    if (mysqli_query($conn, "DELETE FROM product_reviews WHERE review_id=$review_id") == false) { $error = 'Could not delete the review.'; }
    else { $_SESSION['notice'] = 'Review deleted.'; header('Location: reviews.php'); exit; }
}
$reviews = mysqli_query($conn, 'SELECT r.review_id,r.rating,r.comment,r.created_at,p.product_name,c.customer_name FROM product_reviews r JOIN products p ON p.product_id=r.product_id JOIN customers c ON c.customer_id=r.customer_id ORDER BY r.created_at DESC');
include __DIR__ . '/../includes/workspace.php';
?>
<section class="surface records-surface"><div class="section-heading"><div><h2>Moderate reviews</h2><p>Review feedback submitted by customers.</p></div></div>
<div class="table-responsive"><table class="table inventory-table records-table"><thead><tr><th>Product</th><th>Customer</th><th>Rating</th><th>Comment</th><th>Date</th><th>Action</th></tr></thead><tbody>
<?php while ($review = mysqli_fetch_assoc($reviews)) { ?><tr><td><?php echo htmlspecialchars($review['product_name']); ?></td><td><?php echo htmlspecialchars($review['customer_name']); ?></td><td><?php echo $review['rating']; ?> / 5</td><td><?php echo htmlspecialchars($review['comment']); ?></td><td><?php echo htmlspecialchars($review['created_at']); ?></td><td><form method="post"><input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>"><button class="btn btn-sm btn-outline-danger" name="action" value="delete">Delete</button></form></td></tr><?php } ?>
</tbody></table></div><?php if (mysqli_num_rows($reviews) == 0) { ?><p class="empty-state">No reviews to moderate.</p><?php } ?></section>
<?php include __DIR__ . '/../includes/end.php'; ?>
