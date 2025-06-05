<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ডাটাবেস সংযোগ
include 'db_connect.php';

// CLO ও PLO ম্যাপিং রিড করি
$clo_map = $conn->query("SELECT * FROM ece2217 WHERE Roll = 0")->fetch_assoc();
$plo_map = $conn->query("SELECT * FROM ece2217 WHERE Roll = 1")->fetch_assoc();

// শিক্ষার্থীদের মার্ক রিড করি (Roll >= 2000000)
$result = $conn->query("SELECT * FROM ece2217 WHERE Roll >= 2000000");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $roll = $row["Roll"];

        $clo_counts = array_fill(1, 5, 0);  // CLO1–5
        $plo_counts = array_fill(1, 12, 0); // PLO1–12

        // 48টি কলাম নিয়ে কাজ করবো
        for ($i = 1; $i <= 48; $i++) {
            $col = "c$i";
            $mark = floatval($row[$col]);

            // mark যদি 0 হয়, তাহলে এটা গণনায় আনবো না
            if ($mark == 0) continue;

            $clo_num = intval($clo_map[$col]);
            $plo_num = intval($plo_map[$col]);

            if ($clo_num >= 1 && $clo_num <= 5) {
                $clo_counts[$clo_num]++;
            }
            if ($plo_num >= 1 && $plo_num <= 12) {
                $plo_counts[$plo_num]++;
            }
        }

        // ইনসার্ট বা আপডেট করবো CLO_PLO_Table-এ
        $columns = "Roll, " .
            implode(", ", array_map(fn($n) => "CLO$n", range(1, 5))) . ", " .
            implode(", ", array_map(fn($n) => "PLO$n", range(1, 12)));

        $values = "$roll, " .
            implode(", ", array_map(fn($n) => $clo_counts[$n], range(1, 5))) . ", " .
            implode(", ", array_map(fn($n) => $plo_counts[$n], range(1, 12)));

        $update_parts = [];
        for ($i = 1; $i <= 5; $i++) {
            $update_parts[] = "CLO$i = " . $clo_counts[$i];
        }
        for ($i = 1; $i <= 12; $i++) {
            $update_parts[] = "PLO$i = " . $plo_counts[$i];
        }

        $update_sql = "INSERT INTO CLO_PLO_Table ($columns) VALUES ($values)
            ON DUPLICATE KEY UPDATE " . implode(", ", $update_parts);

        if ($conn->query($update_sql) === TRUE) {
            echo "Roll $roll: CLO & PLO count saved.<br>";
        } else {
            echo "Roll $roll: Error - " . $conn->error . "<br>";
        }
    }
} else {
    echo "শিক্ষার্থীদের কোনো রেকর্ড পাওয়া যায়নি।";
}

$conn->close();

// শেষে redirect করবো process_CT_Mark.php-তে
header("Location: process_CT_Mark.php");
exit();
?>
