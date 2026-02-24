<?php
$baseQuery = [
    'tier' => $selectedTier,
    'q' => $searchTerm,
    'per_page' => $perPage
];

$buildCrmUrl = static function (array $overrides = []) use ($baseQuery): string {
    $query = array_merge($baseQuery, $overrides);
    $query = array_filter($query, static fn ($value) => $value !== null && $value !== '');
    return '?' . http_build_query($query);
};
?>

<?php require __DIR__ . '/components/filters.php'; ?>
<?php require __DIR__ . '/components/stats_cards.php'; ?>
<?php require __DIR__ . '/components/customer_table.php'; ?>
