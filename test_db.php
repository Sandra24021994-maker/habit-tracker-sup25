<?php
require_once 'config/db.php';

$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    echo "Connection to the database was successful!";
} else {
    echo "Failed to connect to the database.";
}

