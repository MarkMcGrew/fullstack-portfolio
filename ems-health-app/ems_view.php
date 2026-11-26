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

// Check if an ID was passed in the URL (e.g., ems_view.php?id=1)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $patient_id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT fullname, birthday, address, emergency_name, emergency_phone, medications, allergies, surgeries FROM patients WHERE id = ?");

    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $patient = $result->fetch_assoc();
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMS Emergency Triage View</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .ems-header { background-color: #d9534f; color: white; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .ems-header h2 { color: white; margin: 0; }
        .alert-box { background-color: #fdf2f2; border-left: 6px solid #d9534f; padding: 15px; margin-bottom: 15px; border-radius: 4px; }
        .info-box { background-color: #f8f9fa; border-left: 6px solid #0275d8; padding: 15px; margin-bottom: 15px; border-radius: 4px; }
        .field-label { font-size: 13px; text-transform: uppercase; color: #666; font-weight: bold; margin-bottom: 4px; }
        .field-value { font-size: 18px; color: #111; font-weight: bold; white-space: pre-line; }
    </style>
</head>
<body>
    <div class="form-container">
        <?php if ($patient): ?>
            <div class="ems-header">
                <h2>EMERGENCY MEDICAL RECORD</h2>
                <p style="margin: 5px 0 0 0;">Verified Triage Data</p>
            </div>

            <div class="info-box">
                <div class="field-label">Patient Name & DOB</div>
                <div class="field-value"><?php echo htmlspecialchars($patient['fullname']); ?> (DOB: <?php echo htmlspecialchars($patient['birthday']); ?>)</div>
                <div class="field-label" style="margin-top: 10px;">Home Address</div>
                <div class="field-value" style="font-size: 16px; font-weight: normal;"><?php echo htmlspecialchars($patient['address']); ?></div>
            </div>
			            <div class="info-box" style="border-left-color: #5cb85c; background-color: #f4fbf4;">
                <div class="field-label" style="color: #3d8b3d;">EMERGENCY CONTACT</div>
                <div class="field-value"><?php echo htmlspecialchars($patient['emergency_name']); ?></div>
                <div class="field-value" style="margin-top: 5px;">
                    <a href="tel:<?php echo htmlspecialchars($patient['emergency_phone']); ?>" style="color: #0275d8; text-decoration: none;">
                        <?php echo htmlspecialchars($patient['emergency_phone']); ?>
                    </a>
                </div>
            </div>


            <div class="alert-box">
                <div class="field-label" style="color: #b52b27;">CRITICAL ALLERGIES</div>
                <div class="field-value"><?php echo htmlspecialchars($patient['allergies']); ?></div>
            </div>

            <div class="alert-box" style="border-left-color: #f0ad4e; background-color: #fcf8e3;">
                <div class="field-label" style="color: #8a6d3b;">ACTIVE MEDICATIONS</div>
                <div class="field-value"><?php echo htmlspecialchars($patient['medications']); ?></div>
            </div>

            <div class="info-box">
                <div class="field-label">Past Surgeries & Conditions</div>
                <div class="field-value" style="font-size: 16px; font-weight: normal;"><?php echo htmlspecialchars($patient['surgeries']); ?></div>
            </div>

            <a href="index.html" style="display: inline-block; margin-top: 10px; color: #555;">Return to Intake Portal</a>
        <?php else: ?>
            <h2>Record Not Found</h2>
            <p>No emergency medical record matched that QR code ID.</p>
            <a href="index.html">Return to Intake Portal</a>
        <?php endif; ?>
    </div>
</body>
</html>
