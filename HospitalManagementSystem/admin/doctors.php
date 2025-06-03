<?php
session_start();
require_once '../config/db_connect.php';
// TODO: Add admin authentication check here

// Handle add doctor
if (isset($_POST['add_doctor'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $specialization = $_POST['specialization'];
    try {
        $stmt = $pdo->prepare("INSERT INTO doctors (first_name, last_name, email, phone, specialization) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $email, $phone, $specialization]);
        $success = "Doctor added successfully!";
    } catch(PDOException $e) {
        $error = "Failed to add doctor: " . $e->getMessage();
    }
}
// Handle remove doctor
if (isset($_POST['remove_doctor'])) {
    $doctor_id = $_POST['doctor_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM doctors WHERE doctor_id = ?");
        $stmt->execute([$doctor_id]);
        $success = "Doctor removed successfully!";
    } catch(PDOException $e) {
        $error = "Failed to remove doctor: " . $e->getMessage();
    }
}
// Fetch all doctors
$stmt = $pdo->query("SELECT * FROM doctors ORDER BY doctor_id DESC");
$doctors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Doctors</title>
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
        .doctor-table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        .doctor-table th, .doctor-table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        .doctor-table th { background: #f0f0f0; }
        .form-inline input, .form-inline select { margin-right: 10px; }
        .form-inline { margin-bottom: 20px; }
        .remove-btn { background: #e53935; color: #fff; border: none; padding: 6px 14px; border-radius: 4px; cursor: pointer; }
        .remove-btn:hover { background: #b71c1c; }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="container">
        <h2>Manage Doctors</h2>
        <a href="dashboard.php">&larr; Back to Dashboard</a>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" class="form-inline">
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phone" placeholder="Phone" required>
            <input type="text" name="specialization" placeholder="Specialization" required>
            <button type="submit" name="add_doctor">Add Doctor</button>
        </form>
        <table class="doctor-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Specialization</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($doctors as $doctor): ?>
                <tr>
                    <td><?php echo $doctor['doctor_id']; ?></td>
                    <td>Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                    <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                    <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="doctor_id" value="<?php echo $doctor['doctor_id']; ?>">
                            <button type="submit" name="remove_doctor" class="remove-btn" onclick="return confirm('Are you sure you want to remove this doctor?');">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 