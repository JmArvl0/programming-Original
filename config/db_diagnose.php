<?php

require_once 'database.php';

function diagnoseDBConnection() {
    echo "Starting database connection diagnosis...<br>";

    // Check if constants are defined
    $requiredConstants = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME'];
    foreach ($requiredConstants as $constant) {
        if (!defined($constant)) {
            echo "Error: Constant $constant is not defined.<br>";
            return;
        }
    }

    echo "All required constants are defined.<br>";

    // Attempt to connect to the database
    try {
        $conn = getDBConnection();
        echo "Database connection successful!<br>";

        // Check database encoding
        $charset = $conn->character_set_name();
        echo "Database character set: $charset<br>";

        closeDBConnection($conn);
        echo "Database connection closed successfully.<br>";
    } catch (RuntimeException $e) {
        echo "Error: " . $e->getMessage() . "<br>";
    }
}

diagnoseDBConnection();

?>