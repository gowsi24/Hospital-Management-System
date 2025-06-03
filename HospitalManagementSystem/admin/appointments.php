<?php
session_start();
require_once '../config/db_connect.php';
// TODO: Add admin authentication check here

// Fetch all appointments with doctor and patient info
$stmt = $pdo->prepare("
    SELECT a.*, 
           p.first_name as patient_first_name, p.last_name as patient_last_name, p.email as patient_email,
           d.first_name as doctor_first_name, d.last_name as doctor_last_name, d.specialization
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN doctors d ON a.doctor_id = d.doctor_id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute();
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointments</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: url('https://static.vecteezy.com/system/resources/previews/004/578/683/non_2x/a-patient-consults-a-doctor-and-nurse-free-vector.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .overlay {
            background: rgba(255, 255, 255, 0.85);
            min-height: 100vh;
            width: 100vw;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
        }
        .container {
            position: relative;
            z-index: 2;
        }
        .appointment-table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        .appointment-table th, .appointment-table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        .appointment-table th { background: #f0f0f0; }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="container">
        <h2>Appointment Records</h2>
        <a href="dashboard.php">&larr; Back to Dashboard</a>
        <table class="appointment-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Specialization</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appt): ?>
                <tr>
                    <td><?php echo $appt['appointment_id']; ?></td>
                    <td><?php echo htmlspecialchars($appt['appointment_date']); ?></td>
                    <td><?php echo htmlspecialchars($appt['appointment_time']); ?></td>
                    <td><?php echo htmlspecialchars($appt['patient_first_name'] . ' ' . $appt['patient_last_name']); ?></td>
                    <td>Dr. <?php echo htmlspecialchars($appt['doctor_first_name'] . ' ' . $appt['doctor_last_name']); ?></td>
                    <td><?php echo htmlspecialchars($appt['specialization']); ?></td>
                    <td><?php echo htmlspecialchars($appt['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 