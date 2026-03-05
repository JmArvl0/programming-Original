<?php

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'beyond_the_map');
define('DB_PORT', 3306);

function getDBConnection(): mysqli
{
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    } catch (mysqli_sql_exception $e) {
        throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}

function closeDBConnection(mysqli $conn): void
{
    $conn->close();
}