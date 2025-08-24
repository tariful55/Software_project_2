<?php
session_start();
$table = $_SESSION['selected_course'];


include "db_connect.php";

// Fetch all rows from ece2217
$query = "SELECT * FROM `$table`";
$result = mysqli_query($conn, $query);
mysqli_query($conn, "DELETE FROM summ_{$_SESSION['selected_course']}"); // Clear previous summary
$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

if (count($rows) < 3) {
    die("Not enough rows (need CLO, PLO, Full Marks).");
}

// Get column names and data columns (c1..c48)
$columns = array_keys($rows[0]);
$data_columns = array_slice($columns, 2); // skip Roll and Name

// Extract CLO, PLO and Full Marks rows
$clo_row = $rows[0];
$plo_row = $rows[1];
$full_marks_row = $rows[2];

// Build mappings: column -> CLO and PLO groups
$clo_map = [];
$plo_map = [];

foreach ($data_columns as $col) {
    if (!empty($clo_row[$col])) {
        $clo_key = "CLO" . intval($clo_row[$col]);
        $clo_map[$clo_key][] = $col;
    }
    if (!empty($plo_row[$col])) {
        $plo_key = "PLO" . intval($plo_row[$col]);
        $plo_map[$plo_key][] = $col;
    }
}

// 1. Insert Total Marks row (Full Marks per CLO and PLO)
$clo_totals = [];
foreach ($clo_map as $clo => $cols) {
    $sum = 0;
    foreach ($cols as $col) {
        $sum += isset($full_marks_row[$col]) ? intval($full_marks_row[$col]) : 0;
    }
    $clo_totals[$clo] = $sum;
}

$plo_totals = [];
foreach ($plo_map as $plo => $cols) {
    $sum = 0;
    foreach ($cols as $col) {
        $sum += isset($full_marks_row[$col]) ? intval($full_marks_row[$col]) : 0;
    }
    $plo_totals[$plo] = $sum;
}

// Prepare insert for total marks row
$all_keys = array_merge(array_keys($clo_totals), array_keys($plo_totals));
$all_values = array_merge(array_values($clo_totals), array_values($plo_totals));

$columns_sql = implode(", ", $all_keys);
$values_sql = implode(", ", $all_values);
$insert_sql = "INSERT INTO summ_{$_SESSION['selected_course']} (Roll, Name, $columns_sql) VALUES ('Total', 'Marks', $values_sql);";



if (!mysqli_query($conn, $insert_sql)) {
    die("Error inserting total marks row: " . mysqli_error($conn));
}

// 2. Insert each student’s CLO and PLO sums
for ($i = 3; $i < count($rows); $i++) {  // start after first 3 control rows
    $student = $rows[$i];
    $roll = $student['Roll'];
    $name = $student['Name'];

    // Calculate CLO sums
    $student_clo_scores = [];
    foreach ($clo_map as $clo => $cols) {
        $sum = 0;
        foreach ($cols as $col) {
            $sum += isset($student[$col]) ? intval($student[$col]) : 0;
        }
        $student_clo_scores[$clo] = $sum;
    }

    // Calculate PLO sums
    $student_plo_scores = [];
    foreach ($plo_map as $plo => $cols) {
        $sum = 0;
        foreach ($cols as $col) {
            $sum += isset($student[$col]) ? intval($student[$col]) : 0;
        }
        $student_plo_scores[$plo] = $sum;
    }

    $student_all_keys = array_merge(array_keys($student_clo_scores), array_keys($student_plo_scores));
    $student_all_values = array_merge(array_values($student_clo_scores), array_values($student_plo_scores));

    $student_columns_sql = implode(", ", $student_all_keys);
    $student_values_sql = implode(", ", $student_all_values);

    // Prepare insert for this student
    $student_insert_sql = "INSERT INTO summ_{$_SESSION['selected_course']} (Roll, Name, $student_columns_sql) VALUES ('$roll', '$name', $student_values_sql);";

    if (!mysqli_query($conn, $student_insert_sql)) {
        echo "Error inserting student $roll: " . mysqli_error($conn) . "<br>";
    }
}

echo "✅ All student and total marks rows inserted successfully.";
?>
