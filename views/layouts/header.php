<?php
$baseStyles = [
    'css/style.css'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Beyond The Map', ENT_QUOTES, 'UTF-8'); ?> - Beyond The Map</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <?php foreach (array_merge($baseStyles, $styles ?? []) as $stylePath): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($stylePath, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endforeach; ?>
</head>
<body>
<?php require __DIR__ . '/../../includes/sidebar.php'; ?>
<?php require __DIR__ . '/../../includes/header.php'; ?>
<div id="content-wrapper">
    <div class="content-container">
