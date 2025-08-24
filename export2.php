<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course']) && !empty($_POST['course'])) {
    $course = $_POST['course'];
    $table = "summ_" . $course;

    // Break course into code and series
    $course_parts = explode('_', $course);
    $course_code = strtoupper($course_parts[0]);
    $series = $course_parts[1] ?? '';

    // Fetch total row (for percentage calculation)
    $total_query = mysqli_query($conn, "SELECT * FROM `$table` WHERE Roll = 'Total'");
    if (!$total_query || mysqli_num_rows($total_query) == 0) {
        die("Total row not found in $table");
    }
    $total_row = mysqli_fetch_assoc($total_query);

    // Prepare headers
    $headers = ['Roll', 'Name'];
    $clos = $plos = [];

    for ($i = 1; $i <= 5; $i++) $clos[] = "CLO$i";
    for ($i = 1; $i <= 12; $i++) $plos[] = "PLO$i";

    foreach ($clos as $c) {
        $headers[] = $c;
        $headers[] = "$c (%)";
        $headers[] = "Expected?";
    }

    foreach ($plos as $p) {
        $headers[] = $p;
        $headers[] = "$p (%)";
        $headers[] = "Expected?";
    }

    // Fetch all rows except Total
    $query = mysqli_query($conn, "SELECT * FROM `$table` WHERE Roll != 'Total'");
    if (!$query) {
        die("Query failed: " . mysqli_error($conn));
    }

    $rows = [];

    while ($row = mysqli_fetch_assoc($query)) {
        $line = [$row['Roll'], $row['Name']];

        // CLOs
        foreach ($clos as $c) {
            $got = $row[$c];
            $total = max($total_row[$c], 1);
            $percent = round(($got / $total) * 100, 2);
            $pass = $percent >= 50 ? 'YES' : 'NO';
            array_push($line, $got, "$percent%", $pass);
        }

        // PLOs
        foreach ($plos as $p) {
            $got = $row[$p];
            $total = max($total_row[$p], 1);
            $percent = round(($got / $total) * 100, 2);
            $pass = $percent >= 50 ? 'YES' : 'NO';
            array_push($line, $got, "$percent%", $pass);
        }

        $rows[] = $line;
    }

    // Generate CSV
    $filename = $table . "_analysis.csv";
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    $output = fopen("php://output", "w");

    // Write course header
    fputcsv($output, [$course_code . " - Series " . $series]);
    fputcsv($output, []); // Blank row
    fputcsv($output, $headers);

    foreach ($rows as $r) {
        fputcsv($output, $r);
    }

    fclose($output);
    exit();

} else {
    echo "Invalid request: course name missing or incorrect method.";
}

