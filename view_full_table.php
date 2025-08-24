<?php
include 'nab_bar.php';
$table = $_SESSION['selected_course'];

// use in updates:
$sql = "UPDATE `$table` SET ... WHERE roll = 0";
// and so on

include 'db_connect.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <title>All Semester Marks - `$table`</title>
  <style>
    body { font-family: Arial; background-color: #1e1e1e; color: #fff; padding: 20px; }
    table { border-collapse: collapse; width: 100%; background-color: #2e2e2e; box-shadow: 0 0 10px #4dbf00; }
    th, td { border: 1px solid #4dbf00; padding: 8px; text-align: center; }
    th { background-color: #444; color: #4dbf00; position: sticky; top: 0; }
    tr:nth-child(even) { background-color: #303030; }
  </style>
</head>
<body>
<br><br><br>";

$result = mysqli_query($conn, "SELECT * FROM `$table` ORDER BY roll ASC");

if (mysqli_num_rows($result) > 0) {
    echo "<table><thead><tr>";
    // Table header
    while ($fieldinfo = mysqli_fetch_field($result)) {
        echo "<th>{$fieldinfo->name}</th>";
    }
    echo "</tr></thead><tbody>";

    // Table data
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        foreach ($row as $val) {
            echo "<td>" . htmlspecialchars($val) . "</td>";
        }
        echo "</tr>";
    }

    echo "</tbody></table>";
} else {
    echo "<p>No data found.</p>";
}

echo "</body></html>";
?>
