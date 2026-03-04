<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<?php

require_once __DIR__ . '/controllers/PassportVisaController.php';

$controller = new PassportVisaController();
$controller->index();