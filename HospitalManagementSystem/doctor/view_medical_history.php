<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['doctor_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['patient_id'])) {
    header("Location: dashboard.php");
    exit();
}

$patient_id = $_GET['patient_id'];

// Get patient details
$stmt = $pdo->prepare("
    SELECT first_name, last_name, date_of_birth, phone, address, email
    FROM patients 
    WHERE patient_id = ?
");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if (!$patient) {
    header("Location: dashboard.php");
    exit();
}

// Get patient's medical history
$stmt = $pdo->prepare("
    SELECT mh.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name
    FROM medical_history mh
    JOIN doctors d ON mh.doctor_id = d.doctor_id
    WHERE mh.patient_id = ?
    ORDER BY mh.diagnosis_date DESC
");
$stmt->execute([$patient_id]);
$medical_history = $stmt->fetchAll();

// Get patient's prescriptions
$stmt = $pdo->prepare("
    SELECT p.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name,
           DATE_FORMAT(p.created_at, '%d %M %Y') as formatted_date
    FROM prescriptions p
    JOIN doctors d ON p.doctor_id = d.doctor_id
    WHERE p.patient_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$patient_id]);
$prescriptions = $stmt->fetchAll();

// Get patient's appointments
$stmt = $pdo->prepare("
    SELECT a.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name,
           DATE_FORMAT(a.appointment_date, '%d %M %Y') as formatted_date,
           DATE_FORMAT(a.appointment_time, '%h:%i %p') as formatted_time
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute([$patient_id]);
$appointments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Medical History</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .patient-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .patient-info h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .patient-info p {
            margin: 8px 0;
            font-size: 16px;
        }
        .section-title {
            color: #2c3e50;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
            margin: 30px 0 20px 0;
        }
        .history-section {
            margin-bottom: 40px;
        }
        .table-container {
            overflow-x: auto;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: bold;
        }
        .status-completed {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .status-pending {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        .status-confirmed {
            background-color: #e3f2fd;
            color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="section">
            <h2>Patient Medical History</h2>
            
            <div class="patient-info">
                <h3>Patient Information</h3>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></p>
                        <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($patient['date_of_birth']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?></p>
                    </div>
                    <div>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($patient['address']); ?></p>
                    </div>
                </div>
            </div>

            <div class="history-section">
                <h3 class="section-title">Medical History</h3>
                <?php if (empty($medical_history)): ?>
                    <p>No medical history records found.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Condition</th>
                                    <th>Treatment</th>
                                    <th>Doctor</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($medical_history as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['diagnosis_date']); ?></td>
                                        <td><?php echo htmlspecialchars($record['condition_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['treatment']); ?></td>
                                        <td>Dr. <?php echo htmlspecialchars($record['doctor_first_name'] . ' ' . $record['doctor_last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['notes']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="history-section">
                <h3 class="section-title">Prescriptions History</h3>
                <?php if (empty($prescriptions)): ?>
                    <p>No prescription records found.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Diagnosis</th>
                                    <th>Medications</th>
                                    <th>Instructions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prescriptions as $prescription): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($prescription['formatted_date']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></td>
                                        <td>Dr. <?php echo htmlspecialchars($prescription['doctor_first_name'] . ' ' . $prescription['doctor_last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($prescription['diagnosis']); ?></td>
                                        <td><?php echo htmlspecialchars($prescription['medications']); ?></td>
                                        <td><?php echo htmlspecialchars($prescription['instructions']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="history-section">
                <h3 class="section-title">Appointment History</h3>
                <?php if (empty($appointments)): ?>
                    <p>No appointment records found.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                    <th>Health Issue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appointment['formatted_date']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['formatted_time']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></td>
                                        <td>Dr. <?php echo htmlspecialchars($appointment['doctor_first_name'] . ' ' . $appointment['doctor_last_name']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                                <?php echo htmlspecialchars($appointment['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($appointment['health_issue']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div style="margin-top: 30px;">
                <a href="dashboard.php" class="button">Back to Dashboard</a>
                <button onclick="window.print()" class="button" style="background-color: #2196F3;">Print Medical History</button>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .button {
                display: none;
            }
            .section {
                padding: 0;
            }
            .patient-info {
                background-color: white !important;
                border: 1px solid #ddd;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</body>
</html> 