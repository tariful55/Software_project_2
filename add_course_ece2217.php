<?php
include('db_connect.php');

// SQL query to create the table
$sql = "CREATE TABLE IF NOT EXISTS ece2217 (
    Roll INT PRIMARY KEY,
    Name VARCHAR(255),
";

// Add c1 to c48 columns dynamically
for ($i = 1; $i <= 48; $i++) {
    $sql .= "c$i TINYINT UNSIGNED";
    if ($i != 48) {
        $sql .= ", ";
    }
}

$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Execute the query
if (mysqli_query($conn, $sql)) {
    echo "Table 'ece2217' created successfully.";
} else {
    echo "Error creating table: " . mysqli_error($conn);
}
?>
