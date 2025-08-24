<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
$table = $_SESSION['selected_course'];



include 'db_connect.php';

// Select students with Roll >= 2000000
$sql = "SELECT * FROM `$table` WHERE Roll >= 2000000";
// ...
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $roll = $row["Roll"];

        // CT groups: 4 columns each
        $ct_groups = [
            "CT1" => ["c1", "c2", "c3", "c4"],
            "CT2" => ["c5", "c6", "c7", "c8"],
            "CT3" => ["c9", "c10", "c11", "c12"],
            "CT4" => ["c13", "c14", "c15", "c16"]
        ];

        // Calculate total for each CT (treat missing/null/empty as 0)
        $ct_totals = [];
        foreach ($ct_groups as $ct_name => $cols) {
            $total = 0;
            foreach ($cols as $col) {
                $val = isset($row[$col]) && $row[$col] !== '' ? floatval($row[$col]) : 0;
                $total += $val;
            }
            $ct_totals[$ct_name] = $total;
        }

        // Find the CT group with the lowest total
        $min_cts = array_keys($ct_totals, min($ct_totals));
        $min_ct = $min_cts[0]; // Take the first one if tied

        // Prepare the update query to set that group to 0
        $cols_to_zero = $ct_groups[$min_ct];
        $update_parts = [];
        foreach ($cols_to_zero as $col) {
            $update_parts[] = "$col = 0";
        }

        $update_sql = "UPDATE `$table` SET " . implode(', ', $update_parts) . " WHERE Roll = $roll";
        

        if ($conn->query($update_sql) === TRUE) {
            echo "Roll $roll: $min_ct dropped<br>";
        } else {
            echo "Roll $roll: Error - " . $conn->error . "<br>";
        }
    }
} else {
    echo "শিক্ষার্থীদের কোনো রেকর্ড পাওয়া যায়নি।";
}

$conn->close();
exit();
?>
