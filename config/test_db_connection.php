<?php

require_once 'database.php';

try {
    $conn = getDBConnection();
    echo "Database connection successful!";
    closeDBConnection($conn);
} catch (RuntimeException $e) {
    echo "Error: " . $e->getMessage();
}

?>