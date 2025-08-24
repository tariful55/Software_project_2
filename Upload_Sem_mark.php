<?php
session_start();
$table = $_SESSION['selected_course'];


include 'db_connect.php';

// 1. Store metadata for roll 0 (CLO)
$clo_updates = [];
for ($i = 17; $i <= 48; $i++) {
    $field = "c{$i}_clo";
    if (isset($_POST[$field])) {
        $value = mysqli_real_escape_string($conn, $_POST[$field]);
        $clo_updates[] = "c{$i} = '$value'";
    }
}
if (!empty($clo_updates)) {
    $sql = "UPDATE `$table` SET " . implode(", ", $clo_updates) . " WHERE roll = 0";
    mysqli_query($conn, $sql);
}

// 2. Store metadata for roll 1 (PLO)
$plo_updates = [];
for ($i = 17; $i <= 48; $i++) {
    $field = "c{$i}_plo";
    if (isset($_POST[$field])) {
        $value = mysqli_real_escape_string($conn, $_POST[$field]);
        $plo_updates[] = "c{$i} = '$value'";
    }
}
if (!empty($plo_updates)) {
    $sql = "UPDATE `$table` SET " . implode(", ", $plo_updates) . " WHERE roll = 1";
    mysqli_query($conn, $sql);
}

// 3. Store metadata for roll 2 (Full Marks)
$full_updates = [];
for ($i = 17; $i <= 48; $i++) {
    $field = "c{$i}_full";
    if (isset($_POST[$field]) && $_POST[$field] !== '') {
        $value = floatval($_POST[$field]);
        $full_updates[] = "c{$i} = $value";
    } else {
        $full_updates[] = "c{$i} = NULL";
    }
}
if (!empty($full_updates)) {
    $sql = "UPDATE `$table` SET " . implode(", ", $full_updates) . " WHERE roll = 2";
    mysqli_query($conn, $sql);
}

// 4. Store student data from sXXX fields
$field_data = $_POST;
$field_keys = array_keys($field_data);
$student_data = [];

// Extract sXXX fields only
foreach ($field_keys as $key) {
    if (preg_match('/^s\d+$/', $key)) {
        $student_data[] = $field_data[$key];
    }
}

// Each student has 2 (roll + name) + 32 marks
$columns_per_student = 34;
$total_students = floor(count($student_data) / $columns_per_student);

for ($i = 0; $i < $total_students; $i++) {
    $base = $i * $columns_per_student;
    $roll = mysqli_real_escape_string($conn, $student_data[$base]);
    $name = mysqli_real_escape_string($conn, $student_data[$base + 1]);

    $updates = [];
    for ($j = 0; $j < 32; $j++) {
        $mark = trim($student_data[$base + 2 + $j]);
        $col = "c" . (17 + $j);
        if ($mark === '') {
            $updates[] = "$col = NULL";
        } else {
            $val = floatval($mark);
            $updates[] = "$col = $val";
        }
    }

    if (!empty($updates)) {
        $sql = "UPDATE `$table` SET " . implode(", ", $updates) . " WHERE roll = '$roll'";
        mysqli_query($conn, $sql);
    }
}


//include 'CLO_PLO.php';
include 'load_clo_plo_mark.php' ; // Load CLO and PLO marks
echo "Semester marks uploaded successfully.<br><b>Redirecting to edit page...</b>";

// Redirect after 2 seconds
echo '<meta http-equiv="refresh" content="2;url=Edit_Sem_mark.php">';
?>
