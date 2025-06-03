<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['patient_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['success'])) {
    echo '<div style="background: #d4edda; color: #155724; padding: 10px; margin: 10px 0;">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div style="background: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0;">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}

// Get available doctors
$stmt = $pdo->query("SELECT * FROM doctors");
$doctors = $stmt->fetchAll();

// Get patient's notifications
try {
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE patient_id = ? AND is_read = FALSE 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['patient_id']]);
    $notifications = $stmt->fetchAll();
} catch(PDOException $e) {
    // If table doesn't exist, initialize empty notifications array
    if ($e->getCode() == '42S02') {
        $notifications = [];
        // Create the notifications table if it doesn't exist
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
                notification_id INT PRIMARY KEY AUTO_INCREMENT,
                patient_id INT,
                appointment_id INT,
                message TEXT,
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
                FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch(PDOException $createError) {
            // Log the error but don't show it to the user
            error_log("Failed to create notifications table: " . $createError->getMessage());
        }
    } else {
        // For other database errors, show the error
        $error = "Error fetching notifications: " . $e->getMessage();
        $notifications = [];
    }
}

// Handle appointment booking
if (
    $_SERVER['REQUEST_METHOD'] == 'POST' &&
    isset($_POST['doctor_id'], $_POST['appointment_date'], $_POST['appointment_time'], $_POST['health_issue'])
) {
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $health_issue = $_POST['health_issue'];
    $patient_id = $_SESSION['patient_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, health_issue) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$patient_id, $doctor_id, $appointment_date, $appointment_time, $health_issue]);
        $booking_success = true;
        $booking_message = "Appointment booked successfully!";
    } catch(PDOException $e) {
        $booking_error = "Booking failed: " . $e->getMessage();
    }
}

// Handle appointment status update
if (isset($_POST['update_status'])) {
    $appointment_id = $_POST['appointment_id'];
    $status = $_POST['status'];
    try {
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
        $stmt->execute([$status, $appointment_id]);
        header("Location: dashboard.php");
        exit();
    } catch(PDOException $e) {
        $error = "Update failed: " . $e->getMessage();
    }
}

// Handle marking notifications as read
if (isset($_POST['mark_read'])) {
    $notification_id = $_POST['notification_id'];
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE notification_id = ? AND patient_id = ?");
        $stmt->execute([$notification_id, $_SESSION['patient_id']]);
        header("Location: dashboard.php");
        exit();
    } catch(PDOException $e) {
        $error = "Failed to mark notification as read: " . $e->getMessage();
    }
}

// Get patient's appointments
$stmt = $pdo->prepare("
    SELECT a.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name, d.specialization
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute([$_SESSION['patient_id']]);
$appointments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['patient_name']); ?></h2>
        
        <?php if (!empty($notifications)): ?>
        <div class="notifications-section" style="margin-bottom: 30px;">
            <h3>Notifications</h3>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification" style="background-color: #e3f2fd; padding: 15px; margin-bottom: 10px; border-radius: 5px; position: relative;">
                    <p style="margin: 0;"><?php echo htmlspecialchars($notification['message']); ?></p>
                    <small style="color: #666;"><?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?></small>
                    <form method="POST" style="position: absolute; top: 10px; right: 10px;">
                        <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                        <button type="submit" name="mark_read" style="background: none; border: none; color: #666; cursor: pointer; font-size: 20px;">×</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="book-appointment-section" style="margin-bottom: 30px;">
            <h3>Book New Appointment</h3>
            <form method="POST" action="" style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="doctor_id">Select Doctor:</label>
                    <select name="doctor_id" id="doctor_id" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">Select a doctor</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo $doctor['doctor_id']; ?>">
                                Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?> 
                                (<?php echo htmlspecialchars($doctor['specialization']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="appointment_date">Appointment Date:</label>
                    <input type="date" name="appointment_date" id="appointment_date" required 
                           min="<?php echo date('Y-m-d'); ?>" 
                           style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="appointment_time">Appointment Time:</label>
                    <input type="time" name="appointment_time" id="appointment_time" required 
                           min="09:00" max="17:00" 
                           style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="health_issue">Health Issue/Reason for Visit:</label>
                    <textarea name="health_issue" id="health_issue" required 
                              style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; min-height: 100px;"></textarea>
                </div>
                
                <button type="submit" style="background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
                    Book Appointment
                </button>
            </form>
        </div>
        
        <div class="appointments-section">
            <h3>Your Appointments</h3>
            <?php if (empty($appointments)): ?>
                <p>No appointments found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Doctor</th>
                            <th>Specialization</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                                <td>Dr. <?php echo htmlspecialchars($appointment['doctor_first_name'] . ' ' . $appointment['doctor_last_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['specialization']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                                <td>
                                    <?php if ($appointment['status'] == 'completed'): ?>
                                        <a href="view_prescription.php?appointment_id=<?php echo $appointment['appointment_id']; ?>" class="button">View Prescription</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <a href="logout.php" class="button">Logout</a>
    </div>
    <script>
        <?php if (isset($booking_success) && $booking_success): ?>
            alert("<?php echo $booking_message; ?>");
        <?php endif; ?>
        <?php if (isset($booking_error)): ?>
            alert("<?php echo $booking_error; ?>");
        <?php endif; ?>
    </script>
</body>
</html>
