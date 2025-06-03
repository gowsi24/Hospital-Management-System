<?php
require_once '../config/db_connect.php';

// Fetch doctors for dropdown
$doctor_stmt = $pdo->query("SELECT doctor_id, first_name, last_name, specialization FROM doctors");
$doctor_list = $doctor_stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $date_of_birth = $_POST['date_of_birth'];

    try {
        $stmt = $pdo->prepare("INSERT INTO patients (first_name, last_name, email, password, phone, address, date_of_birth) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $email, $password, $phone, $address, $date_of_birth]);
        header("Location: login.php");
        exit();
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "This email is already registered. Please use a different email or login.";
        } else {
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background: url('https://www.shutterstock.com/image-photo/smiling-healthcare-worker-tablet-supporting-260nw-2519603355.jpg') no-repeat center center fixed; background-size: cover; position: relative;">
    <div style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(255,255,255,0.85); z-index: 0;"></div>
    <div class="container" style="position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; min-height: 100vh;">
        <div style="width:100%; max-width:400px; margin:auto;">
            <h2>Patient Registration</h2>
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
                    <label>Phone:</label>
                    <input type="tel" name="phone" required>
                </div>
                <div class="form-group">
                    <label>Address:</label>
                    <textarea name="address" required></textarea>
                </div>
                <div class="form-group">
                    <label>Date of Birth:</label>
                    <input type="date" name="date_of_birth" required>
                </div>
                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html> 