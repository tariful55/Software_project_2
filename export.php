<?php
session_start();
include 'db_connect.php';

if (!isset($_POST['course']) || empty($_POST['course'])) {
    die("No course selected.");
}

$course = $_POST['course'];
$main_table = $course;
$summary_table = 'summ_' . $course;

// Function to export a table to CSV
function exportTableToCSV($conn, $tableName, $filename) {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"$filename.csv\"");

    $output = fopen("php://output", "w");

    $result = mysqli_query($conn, "SELECT * FROM `$tableName`");
    if (!$result) {
        echo "Error: " . mysqli_error($conn);
        exit;
    }

    // Fetch column headers
    $columns = array();
    while ($fieldinfo = mysqli_fetch_field($result)) {
        $columns[] = $fieldinfo->name;
    }
    fputcsv($output, $columns);

    // Fetch rows
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }

    fclose($output);
}

// Export both tables sequentially
// This trick allows two files to be zipped for download, or generate one combined export
$tempFile = tempnam(sys_get_temp_dir(), 'export_');
$zip = new ZipArchive();
$zip->open($tempFile, ZipArchive::CREATE);

foreach ([$main_table, $summary_table] as $table) {
    ob_start();
    $result = mysqli_query($conn, "SELECT * FROM `$table`");
    $out = fopen('php://output', 'w');

    // Column headers
    $fields = mysqli_fetch_fields($result);
    $headers = array_map(fn($f) => $f->name, $fields);
    fputcsv($out, $headers);

    // Data
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($out, $row);
    }

    fclose($out);
    $csvContent = ob_get_clean();
    $zip->addFromString("$table.csv", $csvContent);
}

$zip->close();

// Return the zip file
header('Content-Type: application/zip');
header('Content-disposition: attachment; filename="' . $course . '_export.zip"');
header('Content-Length: ' . filesize($tempFile));
readfile($tempFile);
unlink($tempFile);
exit;

