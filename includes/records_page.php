<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/record_pages.php';
date_default_timezone_set('Asia/Dhaka');
mysqli_query($conn, "SET time_zone = '+06:00'");
$page = $recordPages[$pageKey];
$pageTitle = $page['title'];
$records = mysqli_fetch_all(mysqli_query($conn, $page['sql']), MYSQLI_ASSOC);
$filterKey = $page['filter'] ?? null;
$filterOptions = $filterKey ? array_unique(array_filter(array_column($records, $filterKey), fn($value) => $value !== null && $value !== '')) : [];
sort($filterOptions);
$itemsByOrder = [];
if (isset($page['items_sql'])) {
    foreach (mysqli_fetch_all(mysqli_query($conn, $page['items_sql']), MYSQLI_ASSOC) as $item) {
        $itemsByOrder[$item['order_id']][] = $item;
    }
}
function record_escape($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitle; ?> | Stockroom</title>
    <?php include __DIR__ . '/admin_css.php'; ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=2">
</head>
<body class="workspace-page">
    <?php include __DIR__ . '/sidebar.php'; include __DIR__ . '/topbar.php'; ?>
    <main class="page-wrapper"><div class="page-content">
        <div class="page-heading"><div><span class="eyebrow"><?php echo $page['group']; ?></span><h1><?php echo $pageTitle; ?><span class="heading-dot">.</span></h1><p><?php echo $page['description']; ?></p></div><a href="<?php echo basename($_SERVER['SCRIPT_NAME']); ?>" class="btn refresh-button"><i class="bx bx-refresh" aria-hidden="true"></i> Refresh</a></div>
        <div class="records-banner"><span class="records-banner-icon"><i class="bx <?php echo $page['icon']; ?>" aria-hidden="true"></i></span><div><strong><?php echo number_format(count($records)); ?></strong><span><?php echo $pageKey === 'reorder_alerts' ? 'products flagged for reorder' : 'records in this view'; ?></span></div><span class="read-only-tag"><i class="bx bx-show" aria-hidden="true"></i> Browse & search</span></div>
        <section class="surface records-surface" aria-label="<?php echo $pageTitle; ?> records">
            <div class="section-heading"><div><h2>All <?php echo strtolower($pageTitle); ?></h2><p>Search any value shown in the table.</p></div><a href="dashboard.php" class="text-link">Back to overview <i class="bx bx-right-arrow-alt" aria-hidden="true"></i></a></div>
            <div class="table-toolbar"><label class="search-box"><i class="bx bx-search" aria-hidden="true"></i><input type="search" id="record-search" placeholder="Search <?php echo strtolower($pageTitle); ?>…" aria-label="Search <?php echo strtolower($pageTitle); ?>"></label><?php if ($filterKey): ?><label class="visually-hidden" for="record-filter"><?php echo $page['columns'][$filterKey]; ?> filter</label><select id="record-filter" class="form-select"><option value="">All <?php echo strtolower($page['columns'][$filterKey]); ?> values</option><?php foreach ($filterOptions as $option): ?><option value="<?php echo record_escape($option); ?>"><?php echo record_escape(str_replace('_', ' ', $option)); ?></option><?php endforeach; ?></select><?php endif; ?><button type="button" class="reset-filters text-link" id="reset-records">Reset</button></div>
            <div class="table-responsive"><table class="table inventory-table records-table"><thead><tr><?php foreach ($page['columns'] as $label): ?><th><?php echo $label; ?></th><?php endforeach; ?><?php if (isset($page['order_key'])): ?><th>Line items</th><?php endif; ?></tr></thead><tbody id="record-rows">
                <?php foreach ($records as $record):
                    $searchValues = array_intersect_key($record, $page['columns']);
                    if (isset($page['order_key'])) {
                        foreach ($itemsByOrder[$record[$page['order_key']]] ?? [] as $searchItem) {
                            $searchValues[] = implode(' ', $searchItem);
                        }
                    }
                ?>
                    <tr data-record-row data-filter="<?php echo record_escape($filterKey ? ($record[$filterKey] ?? '') : ''); ?>" data-search="<?php echo record_escape(strtolower(implode(' ', $searchValues))); ?>">
                    <?php foreach ($page['columns'] as $key => $label): $value = $record[$key] ?? null; ?>
                        <td class="column-<?php echo $key; ?>"><?php if ($value === null || $value === ''): ?><span class="muted-cell">—</span><?php elseif (in_array($key, ['status', 'role', 'movement_type'], true)): ?><span class="record-badge <?php echo in_array($value, ['Inactive','CANCELLED'], true) ? 'muted-badge' : ''; ?>"><?php echo record_escape(str_replace('_', ' ', $value)); ?></span><?php elseif (in_array($key, $page['decimal'] ?? [], true)): ?><?php echo number_format((float) $value, 2); ?><?php elseif ($key === 'quantity_change'): ?><span class="<?php echo (int) $value > 0 ? 'positive' : 'negative'; ?> fw-semibold"><?php echo (int) $value > 0 ? '+' : ''; ?><?php echo (int) $value; ?></span><?php else: ?><?php echo record_escape($value); ?><?php endif; ?></td>
                    <?php endforeach; ?>
                    <?php if (isset($page['order_key'])): $items = $itemsByOrder[$record[$page['order_key']]] ?? []; ?><td><details class="order-items"><summary>View items (<?php echo count($items); ?>)</summary><div class="order-item-content"><?php if (!$items): ?><p>No line items recorded.</p><?php endif; ?><?php foreach ($items as $item): ?><article><strong><?php echo record_escape($item['product']); ?></strong><small><?php echo record_escape($item['sku']); ?></small><p><?php echo (int) $item['quantity']; ?> × <?php echo number_format((float) $item['price'], 2); ?> = <b><?php echo number_format((float) $item['line_total'], 2); ?></b></p><?php if (isset($item['received'])): ?><small>Received: <?php echo (int) $item['received']; ?> of <?php echo (int) $item['quantity']; ?></small><?php endif; ?></article><?php endforeach; ?><small>Line totals before any order-level adjustments.</small></div></details></td><?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <tr id="no-records" <?php echo $records ? 'hidden' : ''; ?>><td colspan="<?php echo count($page['columns']) + (isset($page['order_key']) ? 1 : 0); ?>" class="empty-state"><i class="bx bx-search-alt" aria-hidden="true"></i><p>No records match this view.</p></td></tr>
            </tbody></table></div>
            <div class="table-bottom"><span id="record-count" aria-live="polite"><?php echo count($records); ?> records</span><div class="pagination-controls"><label for="page-size">Rows</label><select id="page-size" class="form-select"><option value="10">10</option><option value="25">25</option><option value="50">50</option></select><button type="button" id="previous-page" class="row-details" aria-label="Previous page"><i class="bx bx-chevron-left" aria-hidden="true"></i></button><span id="page-number">1 / 1</span><button type="button" id="next-page" class="row-details" aria-label="Next page"><i class="bx bx-chevron-right" aria-hidden="true"></i></button></div></div>
        </section>
        <div class="dashboard-bottom"><span><span class="status-dot"></span> Loaded at <?php echo date('H:i'); ?> · Dhaka time</span><span><?php echo isset($page['decimal']) ? 'Amounts use your database prices; no currency conversion.' : 'Display and search only. Your records stay unchanged.'; ?></span></div>
    </div><?php include __DIR__ . '/footer.php'; ?></main>
    <?php include __DIR__ . '/admin_script.php'; ?>
</body>
</html>
