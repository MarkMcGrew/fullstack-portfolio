# Full-Stack GPA & Degree Progress Tracker

An interactive web application built to track college coursework, calculate weighted cumulative GPAs, and visualize degree completion progress toward a 120-credit milestone.

## Features
- **Full CRUD Operations:** Add, edit (course name, credits, or grade), and delete classes with persistent MySQL storage.
- **Dynamic GPA & Progress Math:** Automatically converts percentage grades to a 4.0 plus/minus GPA scale and updates the visual degree progress bar in real time.
- **Flexible Credit Handling:** Supports decimal credit conversions (e.g., `2.67`), transfer credits (`TR`—counted toward degree progress without affecting GPA), and upcoming courses (`N/A`).
- **Security Best Practices:** Uses PHP prepared statements (`mysqli::prepare`) to prevent SQL injection and `htmlspecialchars()` to prevent XSS attacks.

## Tech Stack
- **Frontend:** HTML5, CSS3, Vanilla JavaScript (DOM manipulation & event handling)
- **Backend:** PHP 8+
- **Database:** MySQL / MariaDB

## Local Setup (XAMPP)
1. Copy this folder into your `htdocs` directory.
2. Open **phpMyAdmin** and import `database.sql` to create `gpa_tracker_db` with sample courses.
3. Rename `db.example.php` to `db.php` and verify your MySQL port (`3306` or `3307`).
4. Visit `http://localhost/gpa-tracker/index.php` in your browser.
![GPA Tracker Dashboard](<screenshots/My GPA & Degree Progress Tracker.png>)
