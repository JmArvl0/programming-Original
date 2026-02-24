<?php
$baseQuery = [
    'tab' => $selectedTab,
    'payment' => $selectedPayment,
    'status' => $selectedStatus,
    'q' => $searchTerm,
    'per_page' => $perPage
];

$buildAccountExecutiveUrl = static function (array $overrides = []) use ($baseQuery): string {
    $query = array_merge($baseQuery, $overrides);
    $query = array_filter($query, static fn ($value) => $value !== null && $value !== '');
    return '?' . http_build_query($query);
};
?>

<?php require __DIR__ . '/components/header.php'; ?>
<?php require __DIR__ . '/components/stats.php'; ?>
<div class="table-section">
    <?php require __DIR__ . '/components/filters.php'; ?>
    <?php require __DIR__ . '/components/table.php'; ?>
    <?php require __DIR__ . '/components/pagination.php'; ?>
</div>
