<?php
session_start();
require_once '../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE email = ?");
        $stmt->execute([$email]);
        $patient = $stmt->fetch();

        if ($patient && password_verify($password, $patient['password'])) {
            $_SESSION['patient_id'] = $patient['patient_id'];
            $_SESSION['patient_name'] = $patient['first_name'] . ' ' . $patient['last_name'];
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
    <title>Patient Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background: url('https://www.shutterstock.com/image-photo/smiling-healthcare-worker-tablet-supporting-260nw-2519603355.jpg') no-repeat center center fixed; background-size: cover; position: relative;">
    <div style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(255,255,255,0.85); z-index: 0;"></div>
    <div class="container" style="position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; min-height: 100vh;">
        <div style="width:100%; max-width:400px; margin:auto;">
            <h2>Patient Login</h2>
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