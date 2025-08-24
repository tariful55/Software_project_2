<?php
session_start();
include('db_connect.php');

// Handle course selection (sets session and redirects)
if (isset($_GET['select'])) {
    $_SESSION['selected_course'] = $_GET['select'];
    header("Location: edit_CT_Mark.php");
    exit();
}

// Handle new course addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_code'], $_POST['series'])) {
    $course_code = strtolower(trim($_POST['course_code']));
    $series = trim($_POST['series']);

    // Insert into courses table
    $stmt = $conn->prepare("INSERT INTO courses (course_code, series) VALUES (?, ?)");
    $stmt->bind_param("ss", $course_code, $series);
    $stmt->execute();
    $stmt->close();

    // Create main table: coursecode_series
    $table_name = "{$course_code}_{$series}";
    $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
        Roll INT PRIMARY KEY,
        Name VARCHAR(255), ";
    for ($i = 1; $i <= 48; $i++) {
        $sql .= "c$i TINYINT UNSIGNED";
        if ($i < 48) $sql .= ", ";
    }
    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($conn, $sql);

    // Create summary table: summ_coursecode_series
    $summ_table = "summ_{$course_code}_{$series}";
    $sql2 = "CREATE TABLE IF NOT EXISTS `$summ_table` (
        Roll VARCHAR(20),
        Name VARCHAR(100),
        CLO1 INT, CLO2 INT, CLO3 INT, CLO4 INT, CLO5 INT,
        PLO1 INT, PLO2 INT, PLO3 INT, PLO4 INT, PLO5 INT, PLO6 INT,
        PLO7 INT, PLO8 INT, PLO9 INT, PLO10 INT, PLO11 INT, PLO12 INT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($conn, $sql2);

    echo "<p style='color:lime'>Course '$course_code $series' added and tables created.</p>";
}

// Fetch all existing courses
$courses = mysqli_query($conn, "SELECT * FROM courses ORDER BY si DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Course Manager</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1e1e1e;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            background-color: #2e2e2e;
            box-shadow: 0 0 15px rgba(77, 191, 0, 0.7);
        }
        th, td {
            padding: 10px;
            border: 1px solid #4dbf00;
        }
        th {
            background-color: #444;
            color: #4dbf00;
        }
        a {
            color: #4dbf00;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        input, button {
            padding: 5px;
            margin: 5px;
            background-color: #333;
            color: white;
            border: none;
        }
        button {
            background-color: #4dbf00;
            color: black;
        }
        button:hover {
            background-color: #6fe000;
        }
        #manual-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4dbf00;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
        }
        #manual-panel {
            position: fixed;
            top: 70px;
            right: 20px;
            width: 300px;
            background-color: #2e2e2e;
            border: 1px solid #4dbf00;
            padding: 15px;
            display: none;
            box-shadow: 0 0 10px #4dbf00;
            z-index: 999;
        }
    </style>
</head>
<body>

<h2>👋 Welcome to CLO-PLO Course Management System</h2>
<p>Manage your courses, update assessments, and export results easily.</p>

<div id="manual-btn" title="User Manual">❔</div>
<div id="manual-panel">
    <h3>📘 User Manual</h3>
    <ul>
        <li>Select a course to begin editing marks.</li>
        <li>Click Export to download course data (CTs & CLO-PLO Summary).</li>
        <li>Use the Add Course form to create new course records.</li>
    </ul>
</div>

<h2>📘 Select a Course to Work With</h2>

<table>
    <tr>
        <th>SI</th>
        <th>Course Code</th>
        <th>Series</th>
        <th>Export</th>
        <th>Action</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($courses)): ?>
    <tr>
        <td><?= $row['si'] ?></td>
        <td><?= strtoupper($row['course_code']) ?></td>
        <td><?= $row['series'] ?></td>
        <td>
            <!-- Include Font Awesome once in your page -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<form method="post" action="export2.php" style="display:inline;">
  <input type="hidden" name="course" value="<?= $row['course_code'] . '_' . $row['series'] ?>">
  <button type="submit" style="background:black;color:white;border:none;padding:6px 14px;font-size:14px;cursor:pointer;transition:all 0.3s ease;">
    Export <i class="fas fa-download" style="margin-left:5px;opacity:0;transition:opacity 0.3s;"></i>
  </button>
</form>

<!-- Add this JS to trigger hover effect on icon -->
<script>
  document.querySelectorAll('form button').forEach(btn => {
    btn.addEventListener('mouseover', () => {
      const icon = btn.querySelector('.fa-download');
      if (icon) icon.style.opacity = '1';
    });
    btn.addEventListener('mouseout', () => {
      const icon = btn.querySelector('.fa-download');
      if (icon) icon.style.opacity = '0';
    });
  });
</script>

        </td>
        <td>
            <a href="index.php?select=<?= $row['course_code'] . '_' . $row['series'] ?>">➡️ Select</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<h3>➕ Add New Course</h3>
<form method="POST">
    <input type="text" name="course_code" placeholder="Course Code (e.g. ece2217)" required>
    <input type="text" name="series" placeholder="Series (e.g. 21)" required>
    <button type="submit">Add Course</button>
</form>

<script>
    document.getElementById("manual-btn").onclick = function() {
        const panel = document.getElementById("manual-panel");
        panel.style.display = panel.style.display === "none" ? "block" : "none";
    };
</script>

</body>
</html>
