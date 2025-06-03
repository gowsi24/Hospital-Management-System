<?php
session_start();
// TODO: Add admin authentication check here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-nav { margin: 40px auto; max-width: 400px; text-align: center; }
        .admin-nav a { display: block; margin: 20px 0; padding: 18px; background: #4CAF50; color: #fff; text-decoration: none; border-radius: 8px; font-size: 20px; font-weight: bold; transition: background 0.2s; }
        .admin-nav a:hover { background: #388e3c; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Dashboard</h2>
        <div class="admin-nav">
            <a href="doctors.php">Manage Doctors</a>
            <a href="patients.php">View Patients</a>
            <a href="appointments.php">View Appointments</a>
        </div>
    </div>
</body>
</html> 