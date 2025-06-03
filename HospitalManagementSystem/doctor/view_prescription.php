<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['doctor_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['appointment_id'])) {
    header("Location: dashboard.php");
    exit();
}

$appointment_id = $_GET['appointment_id'];

// Get prescription details
$stmt = $pdo->prepare("
    SELECT p.*, pt.first_name as patient_first_name, pt.last_name as patient_last_name
    FROM prescriptions p
    JOIN patients pt ON p.patient_id = pt.patient_id
    WHERE p.appointment_id = ? AND p.doctor_id = ?
");
$stmt->execute([$appointment_id, $_SESSION['doctor_id']]);
$prescription = $stmt->fetch();

if (!$prescription) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Details</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="section">
            <h2>Prescription Details</h2>
            
            <div class="prescription-details">
                <p><strong>Patient:</strong> <?php echo htmlspecialchars($prescription['patient_first_name'] . ' ' . $prescription['patient_last_name']); ?></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($prescription['created_at']); ?></p>
                
                <div class="form-group">
                    <label>Diagnosis:</label>
                    <div class="prescription-content">
                        <?php echo nl2br(htmlspecialchars($prescription['diagnosis'])); ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Medications:</label>
                    <div class="prescription-content">
                        <?php echo nl2br(htmlspecialchars($prescription['medications'])); ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Instructions:</label>
                    <div class="prescription-content">
                        <?php echo nl2br(htmlspecialchars($prescription['instructions'])); ?>
                    </div>
                </div>
                
                <button onclick="window.print()">Print Prescription</button>
                <a href="dashboard.php" class="button">Back to Dashboard</a>
            </div>
        </div>
    </div>
    
    <style>
        @media print {
            .button, button {
                display: none;
            }
            .prescription-details {
                padding: 20px;
            }
        }
        
        .prescription-content {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-top: 5px;
        }
    </style>
</body>
</html> 