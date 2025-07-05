<?php
include 'db_connect.php'; // Your database connection file
// --- Enable Error Reporting (for debugging) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1); // Log errors to a file
ini_set('error_log', 'php_error.log'); // Specify your error log file path

echo "Script started.<br>"; // Debugging output


// --- Function to safely get data from POST and handle potential nulls ---
function get_safe_post($conn, $key) {
    if (isset($_POST[$key])) {
        // Convert empty strings from text/number inputs to NULL
        $value = trim($_POST[$key]);
        if ($value === '') {
            return null;
        }
        // Return the value as is. Prepared statements handle escaping.
        return $value;
    }
    return null; // Return null if the key is not set
}

// Function to prepare a value for TINYINT UNSIGNED binding
function prepare_tinyint_unsigned_for_binding($value) {
    if ($value === null || $value === '') {
        return null; // Bind as NULL if input is empty or null
    }
    // Attempt to cast to integer
    $intValue = (int)$value;

    // Basic validation for TINYINT UNSIGNED range (0 to 255)
    // Also check if the original value was actually numeric. is_numeric handles floats too,
    // but casting to int truncates, so checking the range after casting is important.
    if (!is_numeric($value) || $intValue < 0 || $intValue > 255) {
        // Handle invalid input - log the error and return null
        error_log("Invalid TINYINT UNSIGNED value received: '" . $value . "'. Returning NULL.");
        return null;
    }

    return $intValue; // Return the integer value for binding
}


// --- Process Metadata (Roll 0, 1, 2) - No changes needed here ---

// Roll 0 (CLO) - Using prepared statement
$clo_data = [];
for ($i = 1; $i <= 16; $i++) {
    $clo_data[] = get_safe_post($conn, "ct" . ceil($i/4) . "_q" . (($i-1)%4 + 1) . "_clo");
}
$clo_values_placeholders = implode(', ', array_fill(0, 16, '?'));
$clo_column_names = [];
for ($i = 1; $i <= 16; $i++) {
    $clo_column_names[] = "c$i";
}
$clo_column_list = implode(', ', $clo_column_names);
$clo_update_clauses = implode(', ', array_map(function($col){ return "$col = VALUES($col)"; }, $clo_column_names));

$sql_clo = "INSERT INTO ece2217 (Roll, Name, $clo_column_list)
            VALUES (?, ?, $clo_values_placeholders)
            ON DUPLICATE KEY UPDATE
            Name = VALUES(Name), $clo_update_clauses;";

$stmt_clo = $conn->prepare($sql_clo);
if ($stmt_clo === false) {
    die("Error preparing CLO statement: " . $conn->error);
}

// Binding types: 'i' for Roll, 's' for Name, 16 's' for CLO strings (assuming CLOs are stored as strings like "CO-1")
$bind_types_clo = 'is' . str_repeat('s', 16);
$bind_params_clo = array_merge([0, get_safe_post($conn, 'row_0_name')], $clo_data);

// Need to pass by reference for bind_param with arrays
$bind_params_clo_ref = [];
foreach ($bind_params_clo as $key => $value) {
    $bind_params_clo_ref[$key] = &$bind_params_clo[$key];
}

if (!empty($bind_params_clo_ref)) { // Ensure there are parameters to bind
     call_user_func_array([$stmt_clo, 'bind_param'], array_merge([$bind_types_clo], $bind_params_clo_ref));
} else {
     error_log("CLO bind_params_clo_ref is empty. Check POST data for CLO.");
}


if ($stmt_clo->execute()) {
    echo "CLO data inserted/updated successfully.<br>";
} else {
    echo "Error inserting/updating CLO data: " . $stmt_clo->error . "<br>";
    error_log("SQL Error inserting CLO: " . $stmt_clo->error);
}
$stmt_clo->close();


// Roll 1 (PLO) - Using prepared statement
$plo_data = [];
for ($i = 1; $i <= 16; $i++) {
    $plo_data[] = get_safe_post($conn, "ct" . ceil($i/4) . "_q" . (($i-1)%4 + 1) . "_plo");
}
$plo_values_placeholders = implode(', ', array_fill(0, 16, '?'));
$plo_column_names = [];
for ($i = 1; $i <= 16; $i++) {
    $plo_column_names[] = "c$i";
}
$plo_column_list = implode(', ', $plo_column_names);
$plo_update_clauses = implode(', ', array_map(function($col){ return "$col = VALUES($col)"; }, $plo_column_names));

$sql_plo = "INSERT INTO ece2217 (Roll, Name, $plo_column_list)
            VALUES (?, ?, $plo_values_placeholders)
            ON DUPLICATE KEY UPDATE
            Name = VALUES(Name), $plo_update_clauses;";

$stmt_plo = $conn->prepare($sql_plo);
if ($stmt_plo === false) {
    die("Error preparing PLO statement: " . $conn->error);
}

// Binding types: 'i' for Roll, 's' for Name, 16 's' for PLO strings (assuming PLOs are stored as strings like "PO-1")
$bind_types_plo = 'is' . str_repeat('s', 16);
$bind_params_plo = array_merge([1, get_safe_post($conn, 'row_1_name')], $plo_data);

$bind_params_plo_ref = [];
foreach ($bind_params_plo as $key => $value) {
    $bind_params_plo_ref[$key] = &$bind_params_plo[$key];
}

if (!empty($bind_params_plo_ref)) { // Ensure there are parameters to bind
    call_user_func_array([$stmt_plo, 'bind_param'], array_merge([$bind_types_plo], $bind_params_plo_ref));
} else {
    error_log("PLO bind_params_plo_ref is empty. Check POST data for PLO.");
}


if ($stmt_plo->execute()) {
    echo "PLO data inserted/updated successfully.<br>";
} else {
    echo "Error inserting/updating PLO data: " . $stmt_plo->error . "<br>";
    error_log("SQL Error inserting PLO: " . $stmt_plo->error);
}
$stmt_plo->close();


// Roll 2 (Full Marks) - Using prepared statement and prepare_tinyint_unsigned_for_binding
$full_marks_data = [];
for ($i = 1; $i <= 16; $i++) {
    $full_marks_data[] = prepare_tinyint_unsigned_for_binding(get_safe_post($conn, "ct" . ceil($i/4) . "_q" . (($i-1)%4 + 1) . "_full"));
}
$full_marks_values_placeholders = implode(', ', array_fill(0, 16, '?'));
$full_marks_column_names = [];
for ($i = 1; $i <= 16; $i++) {
    $full_marks_column_names[] = "c$i";
}
$full_marks_column_list = implode(', ', $full_marks_column_names);
$full_marks_update_clauses = implode(', ', array_map(function($col){ return "$col = VALUES($col)"; }, $full_marks_column_names));

$sql_full_marks = "INSERT INTO ece2217 (Roll, Name, $full_marks_column_list)
                   VALUES (?, ?, $full_marks_values_placeholders)
                   ON DUPLICATE KEY UPDATE
                   Name = VALUES(Name), $full_marks_update_clauses;";

$stmt_full_marks = $conn->prepare($sql_full_marks);
if ($stmt_full_marks === false) {
    die("Error preparing Full Marks statement: " . $conn->error);
}

// Binding types: 'i' for Roll, 's' for Name, 16 'i' for TINYINT UNSIGNED columns
$bind_types_full_marks = 'is' . str_repeat('i', 16);
$bind_params_full_marks = array_merge([2, get_safe_post($conn, 'row_2_name')], $full_marks_data);

$bind_params_full_marks_ref = [];
foreach ($bind_params_full_marks as $key => $value) {
    // Pass the value (which is already null or integer from prepare_tinyint_unsigned_for_binding) by reference
    $bind_params_full_marks_ref[$key] = &$bind_params_full_marks[$key];
}

if (!empty($bind_params_full_marks_ref)) { // Ensure there are parameters to bind
    call_user_func_array([$stmt_full_marks, 'bind_param'], array_merge([$bind_types_full_marks], $bind_params_full_marks_ref));
} else {
    error_log("Full Marks bind_params_full_marks_ref is empty. Check POST data for Full Marks.");
}


if ($stmt_full_marks->execute()) {
    echo "Full Marks data inserted/updated successfully.<br>";
} else {
    echo "Error inserting/updating Full Marks data: " . $stmt_full_marks->error . "<br>";
    error_log("SQL Error inserting Full Marks: " . $stmt_full_marks->error);
}
$stmt_full_marks->close();


// --- Process Student Data (Revised for sX naming) ---

// We need to figure out how many students there are based on the sX naming.
// The HTML generates s1, s2, ..., s18 for the first student, s19, ..., s36 for the second, etc.
// The roll number is sX, name is sX+1, and scores are sX+2 to sX+17.
// The pattern is: Roll (sN), Name (sN+1), Scores (sN+2 to sN+17) where N starts at 1 and increments by 18 for each student.

$student_roll_keys = [];
// Find all the keys that look like student roll numbers (s1, s19, s37, ...)
// We can assume they start with 's' and contain only digits after that.
// A safer way might be to iterate through the expected number of students.
// Let's assume the HTML structure implies a fixed number of students based on the JS.
// The JS generates `numberOfStudents` rows, with `nameCounter` incrementing by 18.
// So, student rolls are s1, s(1+18), s(1+18*2), ...
// Let's determine the number of students from the presence of s1.

$numberOfStudents = 0;
if (isset($_POST['s1'])) { // If the first student's roll is present
    // We need to iterate and check for the existence of the roll number key for each potential student
    $expected_roll_key_start = 1;
    while (isset($_POST['s' . $expected_roll_key_start])) {
        $student_roll_keys[] = $expected_roll_key_start;
        $expected_roll_key_start += 18;
        $numberOfStudents++;
    }
}

echo "Found " . $numberOfStudents . " student rows to process.<br>"; // Debugging

if ($numberOfStudents > 0) {

    // Define database column names for scores
    $table_columns = [];
    for ($i = 1; $i <= 16; $i++) {
        $table_columns[] = "c$i";
    }

    $column_list = implode(', ', $table_columns);
    $placeholders = implode(', ', array_fill(0, count($table_columns), '?')); // Placeholders for scores
    $update_list = implode(', ', array_map(function($col){ return "$col = VALUES($col)"; }, $table_columns));

    // Prepare the statement outside the loop for efficiency
    $sql_student = "INSERT INTO ece2217 (Roll, Name, $column_list)
                    VALUES (?, ?, $placeholders)
                    ON DUPLICATE KEY UPDATE
                    Name = VALUES(Name), $update_list;";

    $stmt = $conn->prepare($sql_student);

    if ($stmt === false) {
        die("Error preparing student statement: " . $conn->error);
    }

    // Determine the binding types for the scores. Use 'i' for TINYINT UNSIGNED.
    $score_bind_types = str_repeat('i', count($table_columns));
    $bind_types = 'is' . $score_bind_types; // 'i' for Roll, 's' for Name, followed by score types

    // Loop through the found student roll keys (s1, s19, s37, ...)
    foreach ($student_roll_keys as $name_start_index) {
        $roll_key = 's' . $name_start_index;
        $name_key = 's' . ($name_start_index + 1);

        // Get Roll and Name
        $safe_roll = get_safe_post($conn, $roll_key);
        $safe_name = get_safe_post($conn, $name_key);

        // Validate Roll (should be an integer)
        if (!filter_var($safe_roll, FILTER_VALIDATE_INT)) {
             error_log("Skipping student row starting with key '" . $roll_key . "': Invalid roll number '" . $safe_roll . "'");
             echo "Skipping student row starting with key '" . $roll_key . "': Invalid roll number '" . $safe_roll . "'.<br>";
             continue; // Skip this row if roll is invalid
        }

        $score_values = [];
        // Loop through the 16 score fields for this student
        for ($i = 0; $i < 16; $i++) {
             $score_key = 's' . ($name_start_index + 2 + $i); // sX+2 to sX+17
             $score = get_safe_post($conn, $score_key);
             // Use the helper function to prepare the score for TINYINT UNSIGNED binding
             $score_values[] = prepare_tinyint_unsigned_for_binding($score);
        }

        // Combine all parameters for binding
        $bind_params = array_merge([$safe_roll, $safe_name], $score_values);

        // Need to pass parameters by reference for bind_param with arrays
        $bind_params_ref = [];
        foreach ($bind_params as $key => $value) {
             $bind_params_ref[$key] = &$bind_params[$key];
        }

        // Bind parameters using call_user_func_array
        if (!empty($bind_params_ref)) { // Ensure there are parameters to bind
            call_user_func_array([$stmt, 'bind_param'], array_merge([$bind_types], $bind_params_ref));

            if ($stmt->execute()) {
                echo "Student data for Roll $safe_roll inserted/updated successfully.<br>";
            } else {
                echo "Error inserting/updating student data for Roll $safe_roll: " . $stmt->error . "<br>";
                // You might want to log the specific error here for debugging
                error_log("SQL Error for Roll $safe_roll: " . $stmt->error);
            }
        } else {
             error_log("Student bind_params_ref is empty for key '" . $roll_key . "'. Check POST data.");
             echo "Skipping student row for key '" . $roll_key . "': No parameters to bind.<br>";
        }
    }

    $stmt->close(); // Close the statement after the loop
} else {
    echo "No student data rows found to process (based on 'sX' keys).<br>";
    error_log("No student data rows found in POST based on 'sX' keys.");
}

//Drop the lowest CT mark

//include 'CLO_PLO.php'; // Include CLO and PLO processing
include 'load_clo_plo_mark.php' ; // Load CLO and PLO marks
//echo '<meta http-equiv="refresh" content="2;url=edit_CT_Mark.php">';
include 'drop_lowest_ct_mark.php';
 header("Location: edit_CT_Mark.php"); // Redirect to the edit__CT_Mark.php page
// --- Close Database Connection ---
$conn->close();
echo "Script finished.<br>Redirecting"; // Debugging output


?>
