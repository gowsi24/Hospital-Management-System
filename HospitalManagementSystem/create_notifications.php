<?php
require_once 'config/db_connect.php';

try {
    // Read and execute the SQL file
    $sql = file_get_contents('create_notifications_table.sql');
    
    // Execute the SQL commands
    $pdo->exec($sql);
    
    echo "Notifications table created successfully!";
} catch(PDOException $e) {
    echo "Error creating notifications table: " . $e->getMessage();
}
?> 