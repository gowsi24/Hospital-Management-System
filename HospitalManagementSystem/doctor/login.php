<?php
session_start();
require_once '../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM doctors WHERE email = ?");
        $stmt->execute([$email]);
        $doctor = $stmt->fetch();

        if ($doctor && password_verify($password, $doctor['password'])) {
            $_SESSION['doctor_id'] = $doctor['doctor_id'];
            $_SESSION['doctor_name'] = $doctor['first_name'] . ' ' . $doctor['last_name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } catch(PDOException $e) {
        $error = "Login failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background: url('https://png.pngtree.com/png-clipart/20200701/original/pngtree-doctor-patient-nurse-nurse-patient-hospital-png-image_5423962.jpg') no-repeat center center fixed; background-size: cover; position: relative;">
    <div style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(255,255,255,0.85); z-index: 0;"></div>
    <div class="container" style="position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; min-height: 100vh;">
        <div style="width:100%; max-width:400px; margin:auto;">
            <h2>Doctor Login</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html> 