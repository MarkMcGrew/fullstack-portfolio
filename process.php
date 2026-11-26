<?php
// 1. Load your local QR code library
include('phpqrcode/qrlib.php');

$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "ems_health_db";
$port = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$new_patient_id = null;
$fullname = "";
$error_msg = "";
$qr_file_path = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $birthday = $_POST['birthday'];
    $address = $_POST['address'];
    $emergency_name = $_POST['emergency_name'];
    $emergency_phone = $_POST['emergency_phone'];
    $medications = $_POST['medications'];
    $allergies = $_POST['allergies'];
    $surgeries = $_POST['surgeries'];

    // Encrypt the PIN before storing it in MySQL
    $pin_hash = password_hash($_POST['pin'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO patients (fullname, birthday, address, emergency_name, emergency_phone, medications, allergies, surgeries, pin_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $fullname, $birthday, $address, $emergency_name, $emergency_phone, $medications, $allergies, $surgeries, $pin_hash);

    if ($stmt->execute()) {
        $new_patient_id = $conn->insert_id;

        // 2. Build the local EMS URL
        $ems_url = "http://localhost/ems-health-app/ems_view.php?id=" . $new_patient_id;

        // 3. Name the local PNG image file
        $qr_file_path = "qr_patient_" . $new_patient_id . ".png";

        // 4. Generate the QR code directly onto your hard drive
        QRcode::png($ems_url, $qr_file_path, QR_ECLEVEL_L, 6);
    } else {
        $error_msg = "Error saving record: " . $stmt->error;
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
    <title>Emergency QR Code Generated</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .qr-card { text-align: center; }
        .ems-link {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 20px;
            background-color: #0275d8;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .print-btn {
            display: inline-block;
            margin-top: 15px;
            margin-left: 10px;
            padding: 12px 20px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .print-btn:hover { background-color: #449d44; }
        .back-link { display: block; margin-top: 20px; color: #555; }

        /* Credit-Card Sized Emergency Wallet Card Preview */
        .wallet-card-wrapper {
            display: flex;
            justify-content: center;
            margin: 25px 0;
        }
        .wallet-card {
            width: 3.375in;
            height: 2.125in;
            border: 2px dashed #333;
            border-radius: 8px;
            padding: 10px 14px;
            box-sizing: border-box;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-align: left;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        .wallet-info {
            flex: 1;
            padding-right: 8px;
        }
        .wallet-badge {
            background-color: #d9534f;
            color: white;
            font-size: 10px;
            font-weight: bold;
            padding: 3px 6px;
            border-radius: 3px;
            display: inline-block;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .wallet-name {
            font-size: 14px;
            font-weight: bold;
            color: #111;
            margin: 0 0 4px 0;
        }
        .wallet-detail {
            font-size: 10px;
            color: #444;
            margin: 2px 0;
            line-height: 1.3;
        }
        .wallet-qr img {
            width: 115px;
            height: 115px;
            display: block;
            border: 1px solid #ccc;
            padding: 3px;
        }

        /* Print Rules: Hides everything except the wallet card when printing */
        @media print {
            body { background: none; padding: 0; margin: 0; }
            .form-container { box-shadow: none; padding: 0; }
            .no-print { display: none !important; }
            .wallet-card { box-shadow: none; margin: 0.5in auto; }
        }
    </style>
</head>
<body>
    <div class="form-container qr-card">
        <?php if ($new_patient_id): ?>
            <div class="no-print">
                <h2>Medical Profile Saved!</h2>
                <p>Keep your <strong>Record ID (#<?php echo $new_patient_id; ?>)</strong> and PIN safe if you need to update your profile later.</p>
                <p>Cut out the wallet card below and keep it next to your ID:</p>
            </div>

            <!-- Printable Credit-Card Sized Emergency Badge -->
            <div class="wallet-card-wrapper">
                <div class="wallet-card">
                    <div class="wallet-info">
                        <div class="wallet-badge">EMERGENCY MEDICAL ID</div>
                        <p class="wallet-name"><?php echo htmlspecialchars($fullname); ?></p>
                        <p class="wallet-detail"><strong>DOB:</strong> <?php echo htmlspecialchars($birthday); ?></p>
                        <p class="wallet-detail"><strong>ICE:</strong> <?php echo htmlspecialchars($emergency_name); ?></p>
                        <p class="wallet-detail"><strong>Tel:</strong> <?php echo htmlspecialchars($emergency_phone); ?></p>
                        <p class="wallet-detail" style="margin-top: 5px; font-weight: bold; color: #d9534f;">EMS: Scan QR for Allergies & Meds</p>
                    </div>
                    <div class="wallet-qr">
                        <img src="<?php echo $qr_file_path; ?>" alt="Emergency Medical QR Code">
                    </div>
                </div>
            </div>

            <div class="no-print">
                <a class="ems-link" href="ems_view.php?id=<?php echo $new_patient_id; ?>">Preview EMS Responder View</a>
                <button class="print-btn" onclick="window.print()">Print Wallet Card</button>
                <a class="back-link" href="index.html">Register Another Profile</a>
            </div>

        <?php elseif ($error_msg): ?>
            <h2>Error</h2>
            <p><?php echo $error_msg; ?></p>
            <a class="back-link" href="index.html">Go Back</a>
        <?php else: ?>
            <p>No form data submitted.</p>
            <a class="back-link" href="index.html">Go to Intake Form</a>
        <?php endif; ?>
    </div>
</body>
</html>
