<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ডাটাবেস সংযোগ
include 'db_connection.php';

// Roll >= 2000000 ধরে নিচ্ছি (আপনি প্রয়োজনমত পরিবর্তন করবেন)
$sql = "SELECT * FROM ece2217 WHERE Roll >= 2000000";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $roll = $row["Roll"];

        // CT গুলোর চার-চারটি কলাম যাচাই - যদি কোনো কলাম ফাঁকা থাকে, এই রোল skip করবো
        $ct_groups = [
            ["c1", "c2", "c3", "c4"],
            ["c5", "c6", "c7", "c8"],
            ["c9", "c10", "c11", "c12"],
            ["c13", "c14", "c15", "c16"]
        ];

        $skip_row = false;
        foreach ($ct_groups as $group) {
            foreach ($group as $col) {
                if (!isset($row[$col]) || $row[$col] === null || $row[$col] === '') {
                    // কোনো ডাটা না থাকলে স্কিপ
                    $skip_row = true;
                    break 2; // দুই লুপই বের হয়ে আসবে
                }
            }
        }

        if ($skip_row) {
            // ফাঁকা ডাটা থাকায় skip করলাম
            echo "Roll $roll: Incomplete data, skipping drop<br>";
            continue;
        }

        // CT মোট নম্বর হিসাব
        $ct_scores = [
            "CT1" => $row["c1"] + $row["c2"] + $row["c3"] + $row["c4"],
            "CT2" => $row["c5"] + $row["c6"] + $row["c7"] + $row["c8"],
            "CT3" => $row["c9"] + $row["c10"] + $row["c11"] + $row["c12"],
            "CT4" => $row["c13"] + $row["c14"] + $row["c15"] + $row["c16"]
        ];

        // সর্বনিম্ন CT খুঁজে বের করি
        $min_cts = array_keys($ct_scores, min($ct_scores));
        $min_ct = $min_cts[0]; // একাধিক হলে প্রথমটা নিলাম

        // ড্রপ করার কলাম নির্ধারণ
        switch ($min_ct) {
            case "CT1":
                $update_cols = "c1=0, c2=0, c3=0, c4=0";
                break;
            case "CT2":
                $update_cols = "c5=0, c6=0, c7=0, c8=0";
                break;
            case "CT3":
                $update_cols = "c9=0, c10=0, c11=0, c12=0";
                break;
            case "CT4":
                $update_cols = "c13=0, c14=0, c15=0, c16=0";
                break;
            default:
                $update_cols = "";
        }

        if (!empty($update_cols)) {
            $update_sql = "UPDATE ece2217 SET $update_cols WHERE Roll = $roll";
            if ($conn->query($update_sql) === TRUE) {
                echo "Roll $roll: $min_ct dropped<br>";
            } else {
                echo "Roll $roll: Error - " . $conn->error . "<br>";
            }
        }
    }
} else {
    echo "শিক্ষার্থীদের কোনো রেকর্ড পাওয়া যায়নি।";
}

header("Location: CLO_PLO.php");
exit();
$conn->close();
?>
