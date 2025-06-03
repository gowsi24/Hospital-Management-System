<?php
session_start();
require_once '../config/db_connect.php';
// TODO: Add admin authentication check here

// Fetch all patients
$stmt = $pdo->query("SELECT * FROM patients ORDER BY patient_id DESC");
$patients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patients</title>
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
        .patient-table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        .patient-table th, .patient-table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        .patient-table th { background: #f0f0f0; }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="container">
        <h2>Patient List</h2>
        <a href="dashboard.php">&larr; Back to Dashboard</a>
        <table class="patient-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Date of Birth</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patients as $patient): ?>
                <tr>
                    <td><?php echo $patient['patient_id']; ?></td>
                    <td><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($patient['email']); ?></td>
                    <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                    <td><?php echo htmlspecialchars($patient['date_of_birth']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 