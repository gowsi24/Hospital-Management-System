<?php
require_once '../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $specialization = $_POST['specialization'];
    $phone = $_POST['phone'];

    try {
        $stmt = $pdo->prepare("INSERT INTO doctors (first_name, last_name, email, password, specialization, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $email, $password, $specialization, $phone]);
        header("Location: login.php");
        exit();
    } catch(PDOException $e) {
        $error = "Registration failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Registration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background: url('https://png.pngtree.com/png-clipart/20200701/original/pngtree-doctor-patient-nurse-nurse-patient-hospital-png-image_5423962.jpg') no-repeat center center fixed; background-size: cover; position: relative;">
    <div style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(255,255,255,0.85); z-index: 0;"></div>
    <div class="container" style="position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; min-height: 100vh;">
        <div style="width:100%; max-width:400px; margin:auto;">
            <h2>Doctor Registration</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label>First Name:</label>
                    <input type="text" name="first_name" required>
                </div>
                <div class="form-group">
                    <label>Last Name:</label>
                    <input type="text" name="last_name" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Specialization:</label>
                    <input type="text" name="specialization" required>
                </div>
                <div class="form-group">
                    <label>Phone:</label>
                    <input type="tel" name="phone" required>
                </div>
                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html> 