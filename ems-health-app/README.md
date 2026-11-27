# Emergency Medical QR Profile App

A full-stack emergency triage web application designed to give paramedics and first responders instant access to critical patient medical records via a scannable QR code.

## Problem Solved
During medical emergencies where a patient is unresponsive, every second counts. This application generates a unique QR code and printable wallet card for each user so EMS personnel can immediately view allergies, current medications, medical history, and emergency contacts without searching for paperwork.

## Key Features
- **Dynamic QR Code Generation:** Automatically generates a unique PNG QR code linked to the patient's triage profile using PHP's `phpqrcode` library and GD graphics extension.
- **Public Read-Only Triage View (`ems_view.php`):** Displays high-contrast, easy-to-read emergency medical details when scanned.
- **PIN-Protected Profile Editing (`edit.php`):** Uses PHP `password_hash()` and `password_verify()` to ensure only the profile owner can modify medical records.
- **Printable Emergency Wallet Card:** Custom `@media print` CSS layout formatted to standard credit-card dimensions for easy carrying.

## Tech Stack
- **Frontend:** HTML5, CSS3 (`@media print` responsive styling), JavaScript
- **Backend:** PHP 8+ (`phpqrcode`, GD Library)
- **Database:** MySQL / MariaDB

## Local Setup (XAMPP)
1. Ensure `extension=gd` is enabled in your XAMPP `php.ini` file and restart Apache.
2. Copy this folder into your `htdocs` directory.
3. Open **phpMyAdmin**, create `ems_health_db`, and import `ems_health_db.sql` to load sample demo patients.
4. Update your MySQL host/port settings in your database configuration file (`config.php` or `db.php`).
5. Visit `http://localhost/ems-health-app/` in your browser.
## Screenshots
![Patient Intake Form](<screenshots/Patient Intake Form.png>)
![Printable Wallet Card & QR Code](<screenshots/Printable Wallet Card & QR Code.png>)
![EMS Triage View](<screenshots/EMS Triage View.png>)
