<?php
require_once 'config/db_connect.php';

try {
    // First, ensure we're using the correct database
    $pdo->exec("USE hospital_management");
    
    // Drop and recreate prescriptions table with ON DELETE CASCADE
    $pdo->exec("DROP TABLE IF EXISTS prescriptions");
    $pdo->exec("CREATE TABLE prescriptions (
        prescription_id INT PRIMARY KEY AUTO_INCREMENT,
        appointment_id INT,
        patient_id INT,
        doctor_id INT,
        diagnosis TEXT,
        medications TEXT,
        instructions TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE,
        FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
        FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Create notifications table
    $pdo->exec("DROP TABLE IF EXISTS notifications");
    $pdo->exec("CREATE TABLE notifications (
        notification_id INT PRIMARY KEY AUTO_INCREMENT,
        patient_id INT,
        appointment_id INT,
        message TEXT,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
        FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Add payment_status column if it doesn't exist
    $pdo->exec("ALTER TABLE appointments ADD COLUMN IF NOT EXISTS payment_status ENUM('unpaid', 'paid') DEFAULT 'unpaid'");
    
    // Add payment_date column if it doesn't exist
    $pdo->exec("ALTER TABLE appointments ADD COLUMN IF NOT EXISTS payment_date DATETIME DEFAULT NULL");
    
    echo "Database updated successfully! Tables have been recreated with proper constraints. Payment columns have been added.";
} catch(PDOException $e) {
    echo "Error updating database: " . $e->getMessage() . "<br>";
    echo "Error code: " . $e->getCode();
}
?> 