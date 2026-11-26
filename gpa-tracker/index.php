<?php
require_once "db.php";

// 1. HANDLE ADDING A NEW CLASS
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "add") {
    $course_name = trim($_POST["course_name"]);
    $credits = (float)$_POST["credits"];
    $grade = strtoupper(trim($_POST["grade"]));

    if ($course_name !== "" && $credits > 0 && $grade !== "") {
        $stmt = $conn->prepare("INSERT INTO courses (course_name, credits, grade) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $course_name, $credits, $grade);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php");
    exit();
}

// 2. HANDLE EDITING COURSE NAME, CREDITS, AND GRADE
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "edit") {
    $id = (int)$_POST["id"];
    $new_course_name = trim($_POST["new_course_name"]);
    $new_credits = (float)$_POST["new_credits"];
    $new_grade = strtoupper(trim($_POST["new_grade"]));

    if ($id > 0 && $new_course_name !== "" && $new_credits > 0 && $new_grade !== "") {
        $stmt = $conn->prepare("UPDATE courses SET course_name = ?, credits = ?, grade = ? WHERE id = ?");
        $stmt->bind_param("sdsi", $new_course_name, $new_credits, $new_grade, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php");
    exit();
}

// 3. HANDLE DELETING A CLASS
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "delete") {
    $id = (int)$_POST["id"];

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php");
    exit();
}

// 4. FETCH ALL SAVED COURSES FROM MYSQL
$result = $conn->query("SELECT id, course_name, credits, grade FROM courses ORDER BY id ASC");
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My GPA & Degree Progress Tracker</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      max-width: 900px;
      margin: 30px auto;
      padding: 0 15px;
    }
    .progress-container {
      width: 100%;
      background-color: #e0e0e0;
      border-radius: 8px;
      overflow: hidden;
      height: 24px;
      margin: 10px 0 20px 0;
    }
    .progress-bar {
      height: 100%;
      width: 0%;
      background-color: #2e7d32;
      color: white;
      text-align: center;
      line-height: 24px;
      font-size: 14px;
      font-weight: bold;
      transition: width 0.3s ease;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      padding: 8px 10px;
      text-align: left;
    }
    button {
      cursor: pointer;
      padding: 4px 8px;
      margin-right: 4px;
    }
    form.inline-form {
      display: inline;
    }
  </style>
</head>
<body>

  <h1>My GPA & Degree Progress Tracker</h1>

  <!-- Summary & Progress Bar -->
  <div id="summary">
    <p>
      <strong>Credits Earned:</strong> <span id="total-credits">0</span> / 120 &nbsp;|&nbsp;
      <strong>Cumulative GPA:</strong> <span id="gpa-display">0.00</span>
    </p>
    <div class="progress-container">
      <div id="degree-progress-bar" class="progress-bar">0%</div>
    </div>
  </div>

  <!-- Add New Class Form -->
  <form method="POST" action="index.php" style="margin-bottom: 15px;">
    <input type="hidden" name="action" value="add">
    <input type="text" name="course_name" placeholder="Course Name (e.g., Transfer Credits)" required>
    <input type="number" name="credits" placeholder="Credits" value="3" step="0.01" min="0.01" max="120" style="width: 80px;" required>
    <input type="text" name="grade" placeholder="Grade (95, TR, or N/A)" style="width: 155px;" required>
    <button type="submit">Add Class</button>
  </form>

  <!-- Grades Table Loaded from MySQL -->
  <table id="grade-table" border="1">
    <thead>
      <tr>
        <th>Course Name</th>
        <th>Credits</th>
        <th>Grade</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($row["course_name"]); ?></td>
          <td><?php echo (float)$row["credits"]; ?></td>
          <td><?php echo htmlspecialchars($row["grade"]); ?></td>
          <td>
            <!-- Edit Full Row Form -->
            <form method="POST" action="index.php" class="inline-form" onsubmit="return promptEditRow(this, '<?php echo htmlspecialchars($row['course_name'], ENT_QUOTES); ?>', '<?php echo (float)$row['credits']; ?>', '<?php echo htmlspecialchars($row['grade'], ENT_QUOTES); ?>');">
              <input type="hidden" name="action" value="edit">
              <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
              <input type="hidden" name="new_course_name" value="">
              <input type="hidden" name="new_credits" value="">
              <input type="hidden" name="new_grade" value="">
              <button type="submit">Edit</button>
            </form>

            <!-- Delete Form -->
            <form method="POST" action="index.php" class="inline-form" onsubmit="return confirm('Remove this class from your tracker?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
              <button type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <script>
    const TARGET_DEGREE_CREDITS = 120;

    // 1. Calculate GPA & Degree Credits Earned (Handles Percentages, TR, and N/A)
    function calculateGPA() {
      const rows = document.querySelectorAll("#grade-table tbody tr");
      let earnedDegreeCredits = 0;
      let gpaCredits = 0;
      let totalQualityPoints = 0;

      rows.forEach(function(row) {
        const credits = Number(row.cells[1].textContent.trim());
        const gradeText = row.cells[2].textContent.trim().toUpperCase();

        if (gradeText === "N/A") {
          return;
        }

        if (gradeText === "TR") {
          earnedDegreeCredits += credits;
          return;
        }

        const percentage = Number(gradeText);
        let gradePoints = 0;

        if (percentage >= 93) {
          gradePoints = 4.0;
        } else if (percentage >= 90) {
          gradePoints = 3.7;
        } else if (percentage >= 87) {
          gradePoints = 3.3;
        } else if (percentage >= 83) {
          gradePoints = 3.0;
        } else if (percentage >= 80) {
          gradePoints = 2.7;
        } else if (percentage >= 77) {
          gradePoints = 2.3;
        } else if (percentage >= 73) {
          gradePoints = 2.0;
        } else if (percentage >= 70) {
          gradePoints = 1.7;
        } else if (percentage >= 67) {
          gradePoints = 1.3;
        } else if (percentage >= 63) {
          gradePoints = 1.0;
        } else if (percentage >= 60) {
          gradePoints = 0.7;
        } else {
          gradePoints = 0.0;
        }

        earnedDegreeCredits += credits;
        gpaCredits += credits;
        totalQualityPoints += (gradePoints * credits);
      });

      let cumulativeGPA = 0;
      if (gpaCredits > 0) {
        cumulativeGPA = totalQualityPoints / gpaCredits;
      }

      document.getElementById("total-credits").textContent = earnedDegreeCredits.toFixed(2).replace(/\.00$/, '');
      document.getElementById("gpa-display").textContent = cumulativeGPA.toFixed(2);

      let percentComplete = Math.min(100, (earnedDegreeCredits / TARGET_DEGREE_CREDITS) * 100);
      const progressBar = document.getElementById("degree-progress-bar");
      progressBar.style.width = percentComplete.toFixed(1) + "%";
      progressBar.textContent = percentComplete.toFixed(1) + "%";
    }

    // 2. Prompt to edit Course Name, Credits, and Grade before submitting to PHP
    function promptEditRow(formElement, currentCourse, currentCredits, currentGrade) {
      const updatedCourse = prompt("Edit Course Name:", currentCourse);
      if (updatedCourse === null || updatedCourse.trim() === "") {
        return false;
      }

      const updatedCredits = prompt("Edit Credits for " + updatedCourse + ":", currentCredits);
      if (updatedCredits === null || updatedCredits.trim() === "" || isNaN(Number(updatedCredits))) {
        return false;
      }

      const updatedGrade = prompt("Edit Grade for " + updatedCourse + " (percentage, TR, or N/A):", currentGrade);
      if (updatedGrade === null || updatedGrade.trim() === "") {
        return false;
      }

      formElement.querySelector('input[name="new_course_name"]').value = updatedCourse.trim();
      formElement.querySelector('input[name="new_credits"]').value = Number(updatedCredits);
      formElement.querySelector('input[name="new_grade"]').value = updatedGrade.trim().toUpperCase();
      return true;
    }

    // Run on page load
    calculateGPA();
  </script>

</body>
</html>
