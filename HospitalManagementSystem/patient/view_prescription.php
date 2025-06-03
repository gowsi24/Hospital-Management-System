<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['patient_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['appointment_id'])) {
    header("Location: dashboard.php");
    exit();
}

$appointment_id = $_GET['appointment_id'];

// Get prescription details with additional patient and appointment information
$stmt = $pdo->prepare("
    SELECT p.*, 
           pt.first_name as patient_first_name, pt.last_name as patient_last_name, 
           pt.date_of_birth,
           d.first_name as doctor_first_name, d.last_name as doctor_last_name, 
           d.specialization,
           DATE_FORMAT(a.appointment_date, '%d %M %Y') as formatted_date,
           DATE_FORMAT(a.appointment_time, '%h:%i %p') as formatted_time,
           DATE_FORMAT(pt.date_of_birth, '%d %M %Y') as formatted_dob,
           DATE_FORMAT(p.created_at, '%d %M %Y %h:%i %p') as prescription_date
    FROM prescriptions p
    JOIN patients pt ON p.patient_id = pt.patient_id
    JOIN doctors d ON p.doctor_id = d.doctor_id
    JOIN appointments a ON p.appointment_id = a.appointment_id
    WHERE p.appointment_id = ? AND p.patient_id = ?
");
$stmt->execute([$appointment_id, $_SESSION['patient_id']]);
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
    <style>
        .prescription-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .prescription-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .prescription-header h2 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .detail-group {
            margin-bottom: 15px;
        }
        .detail-group strong {
            color: #2c3e50;
            display: block;
            margin-bottom: 5px;
        }
        .prescription-content {
            margin-top: 30px;
        }
        .prescription-section {
            margin-bottom: 25px;
        }
        .prescription-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e0e0e0;
        }
        .prescription-section p {
            white-space: pre-line;
            line-height: 1.6;
        }
        .prescription-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 0.9em;
        }
        .print-button {
            background-color: #2196F3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }
        .print-button:hover {
            background-color: #1976D2;
        }
        @media print {
            .no-print {
                display: none;
            }
            .prescription-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="prescription-container">
            <div class="prescription-header">
                <h2>Medical Prescription</h2>
                <div class="details-grid">
                    <div>
                        <div class="detail-group">
                            <strong>Patient Name:</strong>
                            <?php echo htmlspecialchars($prescription['patient_first_name'] . ' ' . $prescription['patient_last_name']); ?>
                        </div>
                        <div class="detail-group">
                            <strong>Date of Birth:</strong>
                            <?php echo htmlspecialchars($prescription['formatted_dob']); ?>
                        </div>
                    </div>
                    <div>
                        <div class="detail-group">
                            <strong>Doctor:</strong>
                            Dr. <?php echo htmlspecialchars($prescription['doctor_first_name'] . ' ' . $prescription['doctor_last_name']); ?>
                        </div>
                        <div class="detail-group">
                            <strong>Specialization:</strong>
                            <?php echo htmlspecialchars($prescription['specialization']); ?>
                        </div>
                        <div class="detail-group">
                            <strong>Appointment Date:</strong>
                            <?php echo htmlspecialchars($prescription['formatted_date'] . ' at ' . $prescription['formatted_time']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="prescription-content">
                <div class="prescription-section">
                    <h3>Diagnosis</h3>
                    <p><?php echo nl2br(htmlspecialchars($prescription['diagnosis'])); ?></p>
                </div>

                <div class="prescription-section">
                    <h3>Medications</h3>
                    <p><?php echo nl2br(htmlspecialchars($prescription['medications'])); ?></p>
                </div>

                <div class="prescription-section">
                    <h3>Instructions</h3>
                    <p><?php echo nl2br(htmlspecialchars($prescription['instructions'])); ?></p>
                </div>
            </div>

            <div class="prescription-footer">
                <p>Prescription issued on: <?php echo htmlspecialchars($prescription['prescription_date']); ?></p>
            </div>

            <div class="no-print" style="text-align: center; margin-top: 20px;">
                <button onclick="window.print()" class="print-button">Print Prescription</button>
                <a href="dashboard.php" class="button" style="margin-left: 10px;">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html> 