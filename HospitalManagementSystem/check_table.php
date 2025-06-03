<?php
require_once 'config/db_connect.php';

try {
    $pdo->exec("USE hospital_management");
    
    // Get table structure
    $stmt = $pdo->query("SHOW COLUMNS FROM appointments");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 