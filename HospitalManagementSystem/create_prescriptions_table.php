<?php
require_once 'config/db_connect.php';

try {
    // First, ensure we're using the correct database
    $pdo->exec("USE hospital_management");
    
    // Create prescriptions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS prescriptions (
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
    
    echo "Prescriptions table has been created successfully!";
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "<br>";
    echo "Error code: " . $e->getCode();
}
?> 