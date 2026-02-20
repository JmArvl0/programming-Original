<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - Beyond The Map</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
    
    <!-- Include Sidebar -->
    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
    
    <!-- Include Header if exists -->
    <?php if(file_exists(__DIR__ . '/../partials/header.php')): ?>
        <?php require_once __DIR__ . '/../partials/header.php'; ?>
    <?php endif; ?>
    
    <!-- MAIN CONTENT WRAPPER -->
    <div id="content-wrapper">
        <div class="content-container container-fluid mt-0"></div>