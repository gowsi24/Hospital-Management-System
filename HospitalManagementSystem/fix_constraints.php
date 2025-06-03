<?php
require_once 'config/db_connect.php';

try {
    // First, ensure we're using the correct database
    $pdo->exec("USE hospital_management");
    
    // Drop the existing foreign key constraint
    $pdo->exec("ALTER TABLE prescriptions DROP FOREIGN KEY prescriptions_ibfk_3");
    
    // Add the new foreign key constraint with ON DELETE CASCADE
    $pdo->exec("ALTER TABLE prescriptions ADD CONSTRAINT prescriptions_ibfk_3 
                FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) 
                ON DELETE CASCADE");
    
    echo "Foreign key constraints have been updated successfully!";
} catch(PDOException $e) {
    echo "Error updating constraints: " . $e->getMessage() . "<br>";
    echo "Error code: " . $e->getCode();
}
?> 