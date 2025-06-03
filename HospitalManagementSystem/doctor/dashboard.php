<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['doctor_id'])) {
    header("Location: login.php");
    exit();
}

// Get doctor's appointments with additional patient details
$stmt = $pdo->prepare("
    SELECT a.*, p.first_name as patient_first_name, p.last_name as patient_last_name, 
           p.patient_id, p.date_of_birth,
           d.specialization,
           DATE_FORMAT(a.appointment_date, '%d %M %Y') as formatted_date,
           DATE_FORMAT(a.appointment_time, '%h:%i %p') as formatted_time,
           DATE_FORMAT(p.date_of_birth, '%d %M %Y') as formatted_dob
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.doctor_id = ?
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");
$stmt->execute([$_SESSION['doctor_id']]);
$appointments = $stmt->fetchAll();

// Handle appointment status update
if (isset($_POST['update_status'])) {
    $appointment_id = $_POST['appointment_id'];
    $status = $_POST['status'];
    try {
        // Get appointment details before updating
        $stmt = $pdo->prepare("
            SELECT a.*, p.patient_id,
                   d.first_name as doctor_first_name, d.last_name as doctor_last_name,
                   DATE_FORMAT(a.appointment_date, '%d %M %Y') as formatted_date,
                   DATE_FORMAT(a.appointment_time, '%h:%i %p') as formatted_time
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            JOIN doctors d ON a.doctor_id = d.doctor_id
            WHERE a.appointment_id = ?
        ");
        $stmt->execute([$appointment_id]);
        $appointment = $stmt->fetch();

        // Update appointment status
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
        $stmt->execute([$status, $appointment_id]);

        // Create notification if appointment is confirmed
        if ($status === 'confirmed' && $appointment) {
            $message = "Your appointment with Dr. " . $appointment['doctor_first_name'] . " " . $appointment['doctor_last_name'] . 
                      " has been confirmed for " . $appointment['formatted_date'] . " at " . $appointment['formatted_time'];
            
            $stmt = $pdo->prepare("INSERT INTO notifications (patient_id, appointment_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$appointment['patient_id'], $appointment_id, $message]);
        }

        header("Location: dashboard.php");
        exit();
    } catch(PDOException $e) {
        $error = "Update failed: " . $e->getMessage();
    }
}

// Handle prescription submission
if (isset($_POST['add_prescription'])) {
    $appointment_id = $_POST['appointment_id'];
    $patient_id = $_POST['patient_id'];
    $diagnosis = $_POST['diagnosis'];
    $medications = $_POST['medications'];
    $instructions = $_POST['instructions'];
    try {
        $stmt = $pdo->prepare("INSERT INTO prescriptions (appointment_id, patient_id, doctor_id, diagnosis, medications, instructions) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$appointment_id, $patient_id, $_SESSION['doctor_id'], $diagnosis, $medications, $instructions]);
        // Update appointment status to completed
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE appointment_id = ?");
        $stmt->execute([$appointment_id]);
        header("Location: dashboard.php");
        exit();
    } catch(PDOException $e) {
        $error = "Prescription failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, Dr. <?php echo htmlspecialchars($_SESSION['doctor_name']); ?></h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <div class="section">
            <h3>Your Appointments</h3>
            <?php if (empty($appointments)): ?>
                <p>No appointments found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Patient Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['formatted_date']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['formatted_time']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']); ?></strong>
                                    <br>
                                    <small style="color: #666;">ID: <?php echo htmlspecialchars($appointment['patient_id']); ?></small>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                        <?php echo htmlspecialchars($appointment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($appointment['status'] == 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" name="update_status">Confirm</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($appointment['status'] == 'confirmed'): ?>
                                        <button onclick="showPrescriptionForm(<?php echo $appointment['appointment_id']; ?>, <?php echo $appointment['patient_id']; ?>)">Add Prescription</button>
                                    <?php endif; ?>
                                    <?php if ($appointment['status'] == 'completed'): ?>
                                        <a href="view_prescription.php?appointment_id=<?php echo $appointment['appointment_id']; ?>" class="button">View Prescription</a>
                                    <?php endif; ?>
                                    <a href="view_medical_history.php?patient_id=<?php echo $appointment['patient_id']; ?>" class="button" style="background-color: #2196F3;">View Medical History</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <!-- Prescription Form Modal -->
        <div id="prescriptionModal" class="modal" style="display: none;">
            <div class="modal-content" style="max-width: 800px;">
                <span class="close">&times;</span>
                <h3>Add Prescription</h3>
                
                <!-- Patient and Appointment Details -->
                <div class="prescription-details" style="background-color: #f8f9fa; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                    <h4 style="margin-top: 0; color: #2c3e50;">Patient & Appointment Information</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <p><strong>Patient Name:</strong> <span id="modal_patient_name"></span></p>
                            <p><strong>Date of Birth:</strong> <span id="modal_patient_dob"></span></p>
                        </div>
                        <div>
                            <p><strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($_SESSION['doctor_name']); ?></p>
                            <p><strong>Specialization:</strong> <span id="modal_doctor_specialization"></span></p>
                            <p><strong>Appointment Date:</strong> <span id="modal_appointment_date"></span></p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="appointment_id" id="modal_appointment_id">
                    <input type="hidden" name="patient_id" id="modal_patient_id">
                    <div class="form-group">
                        <label>Diagnosis:</label>
                        <textarea name="diagnosis" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Medications:</label>
                        <textarea name="medications" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Instructions:</label>
                        <textarea name="instructions" required></textarea>
                    </div>
                    <button type="submit" name="add_prescription">Save Prescription</button>
                </form>
            </div>
        </div>
        <a href="logout.php" class="button">Logout</a>
    </div>
    <script>
        function showPrescriptionForm(appointmentId, patientId) {
            // Find the appointment details from the appointments array
            const appointment = <?php echo json_encode($appointments); ?>.find(a => 
                a.appointment_id == appointmentId && a.patient_id == patientId
            );
            
            if (appointment) {
                // Update modal fields
                document.getElementById('modal_appointment_id').value = appointmentId;
                document.getElementById('modal_patient_id').value = patientId;
                document.getElementById('modal_patient_name').textContent = 
                    appointment.patient_first_name + ' ' + appointment.patient_last_name;
                document.getElementById('modal_patient_dob').textContent = appointment.formatted_dob;
                document.getElementById('modal_doctor_specialization').textContent = appointment.specialization;
                document.getElementById('modal_appointment_date').textContent = 
                    appointment.formatted_date + ' at ' + appointment.formatted_time;
            }
            
            document.getElementById('prescriptionModal').style.display = 'block';
        }
        
        // Close prescription modal when clicking the X
        document.querySelector('.close').onclick = function() {
            document.getElementById('prescriptionModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('prescriptionModal')) {
                document.getElementById('prescriptionModal').style.display = 'none';
            }
        }
    </script>
</body>
</html>
