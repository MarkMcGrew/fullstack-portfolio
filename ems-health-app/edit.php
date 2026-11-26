<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "ems_health_db";
$port = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$patient = null;
$status_msg = "";
$error_msg = "";

// 1. Handle Unlocking the Profile (Requires Record ID + PIN)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['unlock_profile'])) {
    $patient_id = intval($_POST['id']);
    $entered_pin = $_POST['pin'];

    $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify the entered PIN against the encrypted hash in MySQL
        if (password_verify($entered_pin, $row['pin_hash'])) {
            $patient = $row;
        } else {
            $error_msg = "Incorrect Security PIN. Access denied.";
        }
    } else {
        $error_msg = "No patient record found with ID #" . $patient_id;
    }
    $stmt->close();
}

// 2. Handle Saving Updated Profile (Verifies PIN again for security)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $patient_id = intval($_POST['patient_id']);
    $entered_pin = $_POST['pin'];

    // Re-verify PIN before allowing database write
    $check_stmt = $conn->prepare("SELECT pin_hash FROM patients WHERE id = ?");
    $check_stmt->bind_param("i", $patient_id);
    $check_stmt->execute();
    $check_res = $check_stmt->get_result();
    $auth_row = $check_res->fetch_assoc();
    $check_stmt->close();

    if ($auth_row && password_verify($entered_pin, $auth_row['pin_hash'])) {
        $fullname = $_POST['fullname'];
        $birthday = $_POST['birthday'];
        $address = $_POST['address'];
        $emergency_name = $_POST['emergency_name'];
        $emergency_phone = $_POST['emergency_phone'];
        $medications = $_POST['medications'];
        $allergies = $_POST['allergies'];
        $surgeries = $_POST['surgeries'];

        $stmt = $conn->prepare("UPDATE patients SET fullname=?, birthday=?, address=?, emergency_name=?, emergency_phone=?, medications=?, allergies=?, surgeries=? WHERE id=?");
        $stmt->bind_param("ssssssssi", $fullname, $birthday, $address, $emergency_name, $emergency_phone, $medications, $allergies, $surgeries, $patient_id);

        if ($stmt->execute()) {
            $status_msg = "Profile updated successfully! Your existing QR code now reflects these changes.";
        } else {
            $error_msg = "Error updating profile: " . $stmt->error;
        }
        $stmt->close();

        // Reload updated patient data so the form stays open
        $reload = $conn->prepare("SELECT * FROM patients WHERE id = ?");
        $reload->bind_param("i", $patient_id);
        $reload->execute();
        $patient = $reload->get_result()->fetch_assoc();
        $reload->close();
    } else {
        $error_msg = "Security verification failed. Update blocked.";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Emergency Medical Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .success-banner { background-color: #dff0d8; color: #3c763d; padding: 12px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; }
        .error-banner { background-color: #f2dede; color: #a94442; padding: 12px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; }
        .nav-links { margin-top: 20px; text-align: center; }
        .nav-links a { margin: 0 10px; color: #0275d8; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Update Medical Profile</h2>

        <?php if ($status_msg): ?>
            <div class="success-banner"><?php echo htmlspecialchars($status_msg); ?></div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="error-banner"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <?php if (!$patient): ?>
            <!-- Step A: Secure Login Form -->
            <p>Enter your Patient Record ID and Security PIN to unlock your medical profile:</p>
            <form action="edit.php" method="POST">
                <label for="id">Patient Record ID:</label>
                <input type="text" id="id" name="id" required placeholder="e.g., 3">

                <label for="pin">Security PIN:</label>
                <input type="password" id="pin" name="pin" maxlength="6" required placeholder="Enter your 4-6 digit PIN">

                <button type="submit" name="unlock_profile" class="submit-btn" style="background-color: #0275d8;">Unlock Medical Profile</button>
            </form>
        <?php else: ?>
            <!-- Step B: Pre-filled Edit Form -->
            <p>Editing <strong>Record #<?php echo $patient['id']; ?></strong> (Your existing QR code remains active)</p>

            <form action="edit.php" method="POST">
                <input type="hidden" name="patient_id" value="<?php echo $patient['id']; ?>">
                <input type="hidden" name="pin" value="<?php echo htmlspecialchars($_POST['pin']); ?>">

                <fieldset>
                    <legend>Basic Information</legend>
                    <label for="fullname">Full Name:</label>
                    <input type="text" id="fullname" name="fullname" required value="<?php echo htmlspecialchars($patient['fullname']); ?>">

                    <label for="birthday">Date of Birth:</label>
                    <input type="date" id="birthday" name="birthday" required value="<?php echo htmlspecialchars($patient['birthday']); ?>">

                    <label for="address">Home Address:</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($patient['address']); ?>">
                </fieldset>

                <fieldset>
                    <legend>Emergency Contact</legend>
                    <label for="emergency_name">Contact Name & Relationship:</label>
                    <input type="text" id="emergency_name" name="emergency_name" required value="<?php echo htmlspecialchars($patient['emergency_name']); ?>">

                    <label for="emergency_phone">Emergency Phone Number:</label>
                    <input type="tel" id="emergency_phone" name="emergency_phone" required value="<?php echo htmlspecialchars($patient['emergency_phone']); ?>">
                </fieldset>

                <fieldset>
                    <legend>Current Medications</legend>
                    <label for="medications">Medications & Dosages:</label>
                    <textarea id="medications" name="medications" rows="4" required><?php echo htmlspecialchars($patient['medications']); ?></textarea>
                </fieldset>

                <fieldset>
                    <legend>Critical Medical History</legend>
                    <label for="allergies">Medication & Food Allergies:</label>
                    <textarea id="allergies" name="allergies" rows="4" required><?php echo htmlspecialchars($patient['allergies']); ?></textarea>

                    <label for="surgeries">Past Surgeries / Major Conditions:</label>
                    <textarea id="surgeries" name="surgeries" rows="4" required><?php echo htmlspecialchars($patient['surgeries']); ?></textarea>
                </fieldset>

                <button type="submit" name="update_profile" class="submit-btn">Save Updated Medical Profile</button>
            </form>
        <?php endif; ?>

        <div class="nav-links">
            <a href="index.html">New Patient Intake</a>
            <?php if ($patient): ?>
                | <a href="ems_view.php?id=<?php echo $patient['id']; ?>">Preview Updated EMS View</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
